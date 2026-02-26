class MySPA {
  #lang;
  #auth;
  #admin;
  #term;
  #proj;
  #settings;
  #backend;
  #graph;

  #jauge1;
  #jauge2;

  #mqttc;
  #flagloadversion;

  #current;

  constructor() {
    this.#lang=new MyLang();
    this.#auth=new MyAuth(this);
    this.#admin=new MyAdmin();
    this.#proj=new MyPrj(this.#auth);
    this.#settings=new MySettings(this.#auth,this.#proj);
    this.#backend=new MyBackend(this);
    this.#term=new MyTerminal(this,this.#proj);
    this.#graph= new MyGraph();
    this.#lang.langue();
    this.#flagloadversion=0;

    this.#jauge1=document.getElementById("jauge1");
    this.#jauge2=document.getElementById("jauge2");
    
    var clientId = 'br-'+(Date.now()%100000+Math.random());
    var me=this;
    if (location.hostname!='') {
      this.#mqttc=new Paho.MQTT.Client(location.hostname, 443, clientId);
      this.#mqttc.onMessageArrived = function(message) { me._onMessageArrived(message); };
      this.#mqttc.connect({useSSL: true , onSuccess: function() { me._onSuccess();} });
    }
    this.#current=0;
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

  #updatejauge(nbvm) {
    if (nbvm==undefined) return;
    nbvm=parseInt(nbvm);
    if (nbvm==NaN) return;
    if (nbvm<0) nbvm=0;
    if (nbvm>30) nbvm=30;
    this.#jauge1.value=this.#jauge2.innerText=nbvm;
  }

  #loaddb() {
    var xhr= new XMLHttpRequest();
    xhr.open('GET','backend2/db.php', true);
    var me=this;
    xhr.addEventListener('readystatechange', function() { 
      if (xhr.readyState !== XMLHttpRequest.DONE) return;
      if (xhr.status !== 200) return;
      var db=JSON.parse(xhr.responseText);
      me.#proj.buildlist(db);
      me.#updatejauge(db.nbvm);
      me.#settings.update();
    });
    xhr.send();
  }

  _onSuccess() {
    this.#mqttc.subscribe('/all', {qos: 1} );
    this.#mqttc.subscribe('/cnf', {qos: 1} );
    this.#mqttc.subscribe('/met', {qos: 1} );
    if (this.#current!=0) this.#mqttc.subscribe('/prj/'+this.#current, {qos: 1} );
  }

  _onMessageArrived(message) {
    if (message.destinationName=='/cnf')
      this.loadversion();
    else if (message.destinationName=='/all')
      this.#loaddb();
    else if (message.destinationName=='/met')
      this.#graph.update(JSON.parse(message.payloadString),this.#proj.getidprj());
    else if (message.destinationName=='/prj/'+this.#current) {
      if (message.payloadString=='on') {
        this.#term.sendcmd(this.#mqttc,'');
      }
    }
  }

  sendoff(current) { this.#mqttc.send('/prj/'+current,'off',1,false); }

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
    this.#updatejauge(db.nbvm);
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

  adminRefresh1() { this.#admin.refresh1(); this.#admin.refresh5(); }
  adminRefresh2() { this.#admin.refresh2(); }
  adminRefresh3() { this.#admin.refresh3(); }
  adminRefresh5() { this.#admin.refresh5(); }

  sendcmd(cmd) {
    this.#term.sendcmd(this.#mqttc,cmd);
  }

  selectPrj(el) {
    this.#proj.setidprj(el);
    this.#settings.show();
    this.#graph.load(this.#proj.getidprj());
  }

  displayVM() {
    this.#settings.unshow();
    this.#current=this.#term.display(this.#mqttc);

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
  supDpt(el, message) {
    if (confirm(message)==false) return;
    this.#backend.supDpt(this.#proj.supDpt(el));
  }
  addDpt(message) {
    var title=window.prompt(message);
    if (title!='' && title!=null) this.#backend.addDpt(title);
  }
  grpDpt(el) {
    var id=this.#proj.grpDpt(el);
    for (var i=0;i<el.children.length;i++) {
      var selected=el.children[i].selected;
      if (this.#proj.statGrpDpt(id,i,selected))
        this.#backend.grpDpt(id,el.children[i].value,selected);
    }
  }
  delexample(el, message) {
    var id=this.#proj.grpDpt(el.parentNode);
    if (confirm(message)!=false) this.#backend.loadexample(undefined,id);    
  }
  loadexample(el, message) {
    var id=this.#proj.grpDpt(el.parentNode);
    if (confirm(message)!=false) this.#backend.loadexample(el,id);
    el.value='';
  }

  supPrj(message) {
    if (confirm(message)==false) return;
    this.#backend.supPrj(this.#proj.getidprj());
    this.#settings.unshow();
    this.#proj.show();
  }
  addPrj(el, message) {
    var id=this.#proj.addPrj(el);
    var title=window.prompt(message);
    if (title!='' && title!=null) this.#backend.addPrj(id,title);
  }
  savePrj(el) {
    window.open("backend2/saveprj.php?id="+this.#proj.getidprj(),"_blank");
  }

  lock(el) { this.#backend.lock(this.#proj.getidprj(),el.checked); }
  expert(el) { this.#backend.expert(this.#proj.getidprj(),el.checked); }
  renPrj(el) { this.#backend.renPrj(this.#proj.getidprj(),el.value); }
  choiceImage(el) { this.#backend.choiceImage(this.#proj.getidprj(),el.value); }

  startVM() { this.#backend.startVM(this.#proj.getidprj()); }
  stopVM() { this.#backend.stopVM(this.#proj.getidprj()); }
  rollback(el) { this.#backend.rollback(this.#proj.getidprj(),el.parentNode.firstElementChild.value); }

  adminAdd() {
    var title=this.#admin.checkAdd();
    if (title!=false) this.#backend.adminAdd(title);
  }
  adminRm(message) {
    if (confirm(message)==false) return;
    var tab=this.#admin.checkCompileTC();
    if (tab!=false) this.#backend.adminRm(tab[0]);
  }
  adminCompileTC() {
    var tab=this.#admin.checkCompileTC();
    if (tab!=false) this.#backend.adminCompileTC(tab[0],tab[1]);
  }
  adminRmTC(message) {
    if (confirm(message)==false) return;
    var val=document.getElementById('admin_toolchains').value;
    this.#backend.adminRmTC(val)
  }
  adminCompile() {
    var tab=this.#admin.checkCompile();
    if (tab!=false) this.#backend.adminCompile(tab[0],tab[1],tab[2]);
  }
  admin_show_error() {
    this.#admin.show_error();
  }

  speedupRefresh() {
    this.#admin.refresh4();
  }
  speedupAdd() {
   this.#backend.adminSpeedAdd(document.getElementById('speedup_title').value,document.getElementById('speedup_pkgs').value);
  }
  speedupRm(message) {
    if (confirm(message)==false) return;
    this.#backend.adminSpeedRm(document.getElementById('speedup_list').value);
  }

  adminRmImg(el,message) {
    if (confirm(message)==false) return;
    this.#backend.adminRmImg(el.parentNode.parentNode.children[0].value);
  }

  metshow() {
    document.getElementById('cm').checked=true;
  }
  metunshow() {
    document.getElementById('cm').checked=false;
  }
}
