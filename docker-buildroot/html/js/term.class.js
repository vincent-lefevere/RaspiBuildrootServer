class MyTerminal {
  #ws;
  #wsuri;
  #term;
  #callbackclose;
  #proj;
  #last;
  #myinput;

  constructor(proj) {
    this.#proj=proj;
    this.#myinput=document.getElementById('buildrootDisplay');
    this.#term=new Terminal();
    var me=this;
    this.#term.onData(function(event) { me.onData(event); });
    this.#term._publicOptions.scrollback=100000;
    this.#term.open(document.getElementById('terminal'));
    this.#ws=undefined;
    this.#wsuri=undefined;
    this.#last=undefined;
  }

  display() {
    var db=this.#proj.getmydb();
    document.cookie="token="+db.token;
    this.#myinput.checked=true;
    var current=this.#proj.getidprj();
    if (this.#last!=current) {
      if (this.#ws != undefined) this.#ws.close();
      this.#term.clear();
    }
    this.#last=current;
  }

  undisplay() {
    this.#myinput.checked=false;
  }

  sendcmd(uri) {
    var list=document.getElementById('right').classList;
    list.remove('green');
    list.add('red');
    this.#wsuri=uri;
    if (this.#ws != undefined) this.#ws.close();
    else {
      this.#term.clear();
      this.#ws = new WebSocket("wss://"+document.domain+"/BR2-"+this.#proj.getmydb().power+"/"+this.#wsuri);
      var me=this;
      this.#ws.onmessage = function(event) { me.onmessage(event); };
      this.#ws.onclose = function(event) { me.onclose(event); };
      this.#wsuri='';
      this.#term.focus();
    }
  }

  onclose(event) {
    this.#ws=undefined;
    var list=document.getElementById('right').classList;
    list.add('green');
    list.remove('red');
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
