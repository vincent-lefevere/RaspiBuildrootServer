class MySettings {
  #auth;
  #proj;
  #mydiv;
  #myinput;
  #idspan;
  #titlefield;
  #lock
  #tpluser;
  #users;
  #myselect;
  #myversion;
  #tplgitlog;
  #mygitlog;

  constructor(auth,proj) {
    this.#auth=auth;
    this.#proj=proj;
    this.#mydiv=document.getElementById('settings');
    this.#myinput=document.getElementById('settingsDisplay');
    this.#idspan=document.getElementById('settings_id');
    this.#titlefield=document.getElementById('settings_title');
    this.#lock=document.getElementById('settings_lock');
    this.#tpluser=document.getElementById('template_member');
    this.#users=document.getElementById('settings_members');
    this.#myselect=document.getElementById('settings_versions');
    this.#myversion=document.getElementById('settings_version');
    this.#tplgitlog=document.getElementById('template_gitlog');
    this.#mygitlog=document.getElementById('gitlog');
  }

  init(db) {
    this.#myselect.innerHTML='';
    for (var i=0; i<db.images.length; i++) {
      var image=db.images[i];
      var toolchain=db.toolchains.find((el) => el.id==image.toolchain);
      var option=document.createElement('option');
      option.setAttribute('value',db.images[i].id);
      if (image.install==false) option.setAttribute('disabled','');
      var tmp='Buildroot '+db.versions.find((el) => el.id == image.version).title;
      tmp+=' ('+db.defconfs.find((el) => el.id == image.defconf).defconfig;
      tmp+=' - Toolchain '+db.versions.find((el) => el.id == toolchain.version).title;
      tmp+=')'
      option.innerText=tmp;
      this.#myselect.appendChild(option);
    }
  }

  show() {
    var db=this.#proj.getmydb();
    if (db==undefined) return this.unshow();
    this.#idspan.innerHTML=db.id;
    this.#lock.checked=db.lock;
    this.#titlefield.value=db.title;
    this.#titlefield.setAttribute('readonly','');
    this.#mydiv.className=(db.power!==false)?'on':'off';
    this.#users.innerHTML='';
    for (var i=0; i<this.#myselect.childNodes.length; i++) if (this.#myselect.childNodes[i].value==db.version) {
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
    for (var i=0; i<db.users.length; i++) {
      var user=db.users[i];
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
    document.getElementById('settings_sftp').innerHTML=2200+db.power;
    this.#myinput.checked=true;
    this.#mygitlog.innerHTML='';
    this.#mygitlog.style.display='none';
    if (db.history!=undefined) {
      var j=0;
      for (var i=0; i<db.history.length; i++) {
        this.#mygitlog.style.display='';
        var tmp=this.#tplgitlog.cloneNode(true);
        tmp.removeAttribute('id');
        var span=document.createElement('span');
        span.innerHTML=db.history[i].replaceAll('\n','<br/>');
        tmp.appendChild(span);
        var pre=document.createElement('pre');
        for (i++;db.history[i]!='' && i<db.history.length;i++) pre.innerHTML+=db.history[i]+'\n';
        tmp.appendChild(pre);
        tmp.children[0].value=j;
        this.#mygitlog.appendChild(tmp);
        j++;
      }
    }
    return true;
  }

  unshow() { this.#myinput.checked=false; }
}
