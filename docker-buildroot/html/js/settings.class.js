class MySettings {
  #auth;
  #proj;
  #mydiv;
  #myinput;
  #idspan;
  #titlefield;
  #lock;
  #expert_prof;
  #expert_student;
  #tpluser;
  #users;
  #myselect;
  #myversion;
  #tplgitlog;
  #mygitlog;
  #db;
  #prjdb;

  constructor(auth,proj) {
    this.#auth=auth;
    this.#proj=proj;
    this.#mydiv=document.getElementById('settings');
    this.#myinput=document.getElementById('settingsDisplay');
    this.#idspan=document.getElementById('settings_id');
    this.#titlefield=document.getElementById('settings_title');
    this.#lock=document.getElementById('settings_lock');
    this.#expert_prof=document.getElementById('settings_expert_prof');
    this.#expert_student=document.getElementById('settings_expert_student');
    this.#tpluser=document.getElementById('template_member');
    this.#users=document.getElementById('settings_members');
    this.#myselect=document.getElementById('settings_versions');
    this.#myversion=document.getElementById('settings_version');
    this.#tplgitlog=document.getElementById('template_gitlog');
    this.#mygitlog=document.getElementById('gitlog');
  }

  init(db) {
        this.#db=db;
        if (this.#prjdb!=undefined) this.#init(this.#prjdb.expert);
  }
  #init(expert) {
    var flag=false;
    this.#myselect.innerHTML='';
    for (var i=0; i<this.#db.images.length; i++) {
      var image=this.#db.images[i];
      var toolchain=this.#db.toolchains.find((el) => el.id==image.toolchain);
      var option=document.createElement('option');
      option.setAttribute('value',image.id);
      if (image.id==this.#prjdb.version) option.setAttribute('selected','');
      if (!expert && image.speedup==1) option.style.display='none';
      else if (image.install==false) option.setAttribute('disabled','');
      else flag=true;
      var tmp='Buildroot '+this.#db.versions.find((el) => el.id == image.version).title;
      tmp+=' ('+this.#db.defconfs.find((el) => el.id == image.defconf).defconfig;
      tmp+=' - Toolchain '+this.#db.versions.find((el) => el.id == toolchain.version).title;
      tmp+=') ['+this.#db.speedups.find((el) => el.id == image.speedup).title+']';
      option.innerText=tmp;
      this.#myselect.appendChild(option);
    }
    document.getElementById('poweron').style.display=(flag)?'':'none';
  }

  update() {
    this.#prjdb=this.#proj.getmydb();
    if (this.#prjdb==undefined) return this.unshow();
    this.#init(this.#prjdb.expert);
    this.#idspan.innerHTML=this.#prjdb.id;
    this.#lock.checked=this.#prjdb.lock;
    this.#expert_prof.checked=this.#prjdb.expert;
    this.#expert_student.style.visibility=this.#prjdb.expert?'visible':'hidden';
    this.#titlefield.value=this.#prjdb.title;
    this.#titlefield.setAttribute('readonly','');
    this.#mydiv.className=(this.#prjdb.power!==false)?'on':'off';
    this.#users.innerHTML='';
    for (var i=0; i<this.#myselect.childNodes.length; i++) if (this.#myselect.childNodes[i].value==this.#prjdb.version) {
      this.#myversion.value=this.#myselect.childNodes[i].innerHTML;
      this.#myselect.selectedIndex=i;
    }
    var tmp=this.#tpluser.cloneNode(true);
    tmp.setAttribute('id','me');
    var login=this.#auth.getlogin();
    tmp.children[3].value=login;
    tmp.children[2].value=this.#auth.getname();
    this.#users.appendChild(tmp);
    this.#users.className='';
    for (var i=0; i<this.#prjdb.users.length; i++) {
      var user=this.#prjdb.users[i];
      if (user.email!=login) {
        var tmp=this.#tpluser.cloneNode(true);
        tmp.removeAttribute('id');
        tmp.children[3].value=user.email;
        tmp.children[2].value=user.name;
        tmp.className=user.in?'in':'out';
        this.#users.appendChild(tmp);  
      } else if (user.in) {
        this.#mydiv.classList.add('in');
        this.#titlefield.removeAttribute('readonly');
      } else {
        this.#mydiv.classList.add('out');
      }
    }
    if (this.#auth.getprof()) { 
      this.#mydiv.classList.add('prof');
      this.#titlefield.removeAttribute('readonly');
    }
    document.getElementById('settings_sftp').innerHTML=2200+this.#prjdb.power;
    this.#mygitlog.innerHTML='';
    this.#mygitlog.style.display='none';
    if (this.#prjdb.history!=undefined) {
      var j=0;
      for (var i=0; i<this.#prjdb.history.length; i++) {
        this.#mygitlog.style.display='';
        var tmp=this.#tplgitlog.cloneNode(true);
        tmp.removeAttribute('id');
        var span=document.createElement('span');
        span.innerHTML=this.#prjdb.history[i].replaceAll('\n','<br/>');
        tmp.appendChild(span);
        var pre=document.createElement('pre');
        for (i++;this.#prjdb.history[i]!='' && i<this.#prjdb.history.length;i++) pre.innerHTML+=this.#prjdb.history[i]+'\n';
        tmp.appendChild(pre);
        tmp.children[0].value=j;
        this.#mygitlog.appendChild(tmp);
        j++;
      }
    }
    return true;
  }

  show() {
    var ret=this.update();
    if (ret) this.#myinput.checked=true;
    return ret;
  }

  unshow() { this.#myinput.checked=false; }
}
