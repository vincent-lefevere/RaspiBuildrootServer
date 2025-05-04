class MySPA {
  #lang;
  #auth;
  #admin;
  #term;
  #proj;
  #settings;
  #backend;
  #graph;

  #mqttc;
  #flagloadversion;

  constructor() {
    this.#lang=new MyLang();
    this.#auth=new MyAuth(this);
    this.#admin=new MyAdmin();
    this.#proj=new MyPrj(this.#auth);
    this.#settings=new MySettings(this.#auth,this.#proj);
    this.#backend=new MyBackend(this);
    this.#term=new MyTerminal(this.#proj);
    this.#graph= new MyGraph();
    this.#lang.langue();
    this.#flagloadversion=0;
    var clientId = 'br-'+(Date.now()%100000+Math.random());
    var me=this;
    if (location.hostname!='') {
      this.#mqttc=new Paho.MQTT.Client(location.hostname, 443, clientId);
      this.#mqttc.onMessageArrived = function(message) { me._onMessageArrived(message); };
      this.#mqttc.connect({useSSL: true , onSuccess: function() { me._onSuccess();} });      
    }
    this.loadversion();
  }

  loadversion() {
    if (this.#flagloadversion==0) {
      this.#flagloadversion=1;
      var xhr= new XMLHttpRequest();
      xhr.open('GET','backend2/version.php', true);
      var me=this;
      xhr.addEventListener('readystatechange', function() { me.callbackversion(xhr); });
      xhr.send();
    } else this.#flagloadversion=2;
  }  

  #loaddb() {
    var xhr= new XMLHttpRequest();
    xhr.open('GET','backend2/db.php', true);
    var me=this;
    xhr.addEventListener('readystatechange', function() { 
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      if (xhr.status !== 200) return;
      me.#proj.buildlist(JSON.parse(xhr.responseText));
      if (me.#settings.show()==undefined) me.#proj.show();
    });
    xhr.send();
  }

  _onSuccess() {
    this.#mqttc.subscribe('/all', {qos: 1} );
    this.#mqttc.subscribe('/cnf', {qos: 1} );
    this.#mqttc.subscribe('/met', {qos: 1} );
  }

  _onMessageArrived(message) {
    if (message.destinationName=='/cnf') this.loadversion();
    if (message.destinationName=='/all') this.#loaddb();
    if (message.destinationName=='/met') this.#graph.update(JSON.parse(message.payloadString),this.#proj.getidprj());
  }

  langue() { this.#lang.langue(); }
  login() { 
    if (this.#auth.isnew()) this.logout();
    this.#auth.login();
  }
  checkchangepasswd() { this.#auth.checkchangepasswd(); }
  changepasswd() { this.#auth.changepasswd(); }
  logout() {
    this.#proj.show();
    this.#settings.unshow();
    this.#term.undisplay();
    this.#auth.logout();
  }

  callbackversion(xhr) {
    if (xhr.readyState !== XMLHttpRequest.DONE) return;
    var flag=(this.#flagloadversion==2);
    this.#flagloadversion=0;
    if (xhr.status !== 200) return;
    var db=JSON.parse(xhr.responseText);
    this.#settings.init(db);
    this.#admin.init(db);
    if (flag) this.loadversion();
    document.body.children[0].className=(db.images.find((el) => !el.install)!=undefined || db.toolchains.find((el) => !el.install)!=undefined)?'install':'';
    document.body.children[1].className=(db.images.find((el) => el.install)!=undefined)?'install':'';
  }

  callbacklogin(db) {
    this.#proj.buildlist(db);
    this.#proj.changelogin();
  }

  settingsBack() {
    this.#settings.unshow();
    this.#proj.show();
  }

  adminShow() {
    this.#proj.unshow();
    this.#admin.show();
  }

  adminBack() {
    this.#admin.unshow();
    this.#proj.show();
  }

  adminRefresh1() { this.#admin.refresh1(); }
  adminRefresh2() { this.#admin.refresh2(); }

  sendcmd(cmd) { this.#term.sendcmd(cmd); }

  selectPrj(el) {
    this.#proj.setidprj(el);
    this.#settings.show();
    this.#graph.load(this.#proj.getidprj());
  }

  displayVM() {
    this.#settings.unshow();
    this.#term.display();
  }

  buildrootBack() {
    this.#term.undisplay();
    this.#settings.show();
  }

  updateprj(db) {
    this.#proj.buildlist(db);
    this.#settings.show();
  }
  enroll(el) { this.#backend.enroll(el.parentNode.lastElementChild.value,this.#proj.getidprj()); }
  unenroll(el) { this.#backend.unenroll(el.parentNode.lastElementChild.value,this.#proj.getidprj()); }

  loadusers(el) {
    this.#backend.loadusers(el);
    el.value='';
  }
 
  updatedpt(db) {
    this.#proj.buildlist(db);

  }
  supDpt(el) { this.#backend.supDpt(this.#proj.supDpt(el)); }
  addDpt() {
    var title=window.prompt('Titre du nouveau département');
    if (title!='' && title!=null) this.#backend.addDpt(title);
  }
  supPrj() {
    this.#backend.supPrj(this.#proj.getidprj());
    this.#settings.unshow();
    this.#proj.show();
  }
  addPrj(el) {
    var id=this.#proj.addPrj(el);
    var title=window.prompt('Titre du nouveau projet');
    if (title!='' && title!=null) this.#backend.addPrj(id,title);
  }

  lock(el) { this.#backend.lock(this.#proj.getidprj(),el.checked); }
  renPrj(el) { this.#backend.renPrj(this.#proj.getidprj(),el.value); }
  choiceImage(el) { this.#backend.choiceImage(this.#proj.getidprj(),el.value); }

  startVM() { this.#backend.startVM(this.#proj.getidprj()); }
  stopVM() { this.#backend.stopVM(this.#proj.getidprj()); }
  rollback(el) { this.#backend.rollback(this.#proj.getidprj(),el.parentNode.firstElementChild.value); }

  adminAdd() {
    var title=this.#admin.checkAdd();
    if (title!=false) this.#backend.adminAdd(title);
  }

  adminCompileTC() {
    var tab=this.#admin.checkCompileTC();
    if (tab!=false) this.#backend.adminCompileTC(tab[0],tab[1]);
  }

  adminCompile() {
    var tab=this.#admin.checkCompile();
    if (tab!=false) this.#backend.adminCompile(tab[0],tab[1],tab[2]);
  }


  admin_show_error() {
    this.#admin.show_error();
  }

  metshow() {
    document.getElementById('cm').checked=true;
  }
  metunshow() {
    document.getElementById('cm').checked=false;
  }
}
