class MyTerminal {
  #ws;
  #term;
  #callbackclose;
  #spa;
  #proj;
  #last;
  #myinput;
  #myright;

  constructor(spa,proj) {
    this.#spa=spa;
    this.#proj=proj;
    this.#myinput=document.getElementById('buildrootDisplay');
    this.#myright=document.getElementById('right');
    this.#term=new Terminal();
    var me=this;
    this.#term.onData(function(event) { me.onData(event); });
    this.#term._publicOptions.scrollback=100000;
    this.#term.open(document.getElementById('terminal'));
    this.#ws=undefined;
    this.#last=undefined;
  }

  display(mqttc) {
    var db=this.#proj.getmydb();
    if (db.allow) this.#myright.classList.add('expert');
    else this.#myright.classList.remove('expert');
    document.cookie="token="+db.token;
    this.#myinput.checked=true;
    var current=this.#proj.getidprj();
    if (this.#last!=current) {
      mqttc.subscribe('/prj/'+current, {qos: 1} );
      if (this.#last!=undefined) mqttc.unsubscribe('/prj/'+this.#last);
      if (this.#ws!=undefined) this.#ws.close();
      this.#term.reset();
      this.#term.clear();
    }
    mqttc.send('/prj/'+current,'?',1,false);
    this.#last=current;
    return(current);
  }

  undisplay() {
    this.#myinput.checked=false;
  }

  sendcmd(mqttc,uri) {
    Array.from(this.#myright.getElementsByTagName('input')).forEach( (el) => { el.disabled=true;});
    if (this.#ws != undefined) return;
    var me=this;
    this.#ws = new WebSocket("wss://"+document.domain+"/BR2-"+this.#proj.getmydb().power+"/"+uri);
    this.#term.reset();
    this.#term.clear();
    this.#ws.onmessage = function(event) { me.onmessage(event); };
    this.#ws.onclose = function(event) { me.onclose(event); };
    this.#term.focus();
    mqttc.send('/prj/'+this.#proj.getidprj(),'on',1,false);
  }

  onclose(event) {
    console.log(event);
    this.#ws=undefined;
    Array.from(this.#myright.getElementsByTagName('input')).forEach( (el) => { el.disabled=false;});
    this.#spa.sendoff(this.#last);
  }

  onmessage(evt) {
    
    var reader = new FileReader();
    var me=this;
    reader.addEventListener("loadend", function() {
      var msg = reader.result;
      me.#term.write(msg.replaceAll('\n','\n\r'));
    });
    reader.readAsText(evt.data);
  }

  onData(chunk) {
    if (this.#ws != undefined && this.#ws.readyState==1) this.#ws.send(chunk);
  }
}
