class MyAdmin {
  #mydiv;
  #defconfs;
  #versions;
  #versionsfortc;
  #toolchains;
  #speedups;
  #db;

  constructor() {
    this.#mydiv=document.getElementById('admin');
    this.#defconfs=document.getElementById('admin_defconfs');
    this.#versions=document.getElementById('admin_versions');
    this.#versionsfortc=document.getElementById('admin_versions_for_tc');
    this.#speedups=document.getElementById('speedup_list');
    this.#toolchains=document.getElementById('admin_toolchains');
  }

  init(db) {
    this.#db=db;
    this.#defconfs.innerHTML='';
    if (db.defconfs.length==0) this.#defconfs.setAttribute('disabled','');
    else this.#defconfs.removeAttribute('disabled');
    for (var i=0; i<db.defconfs.length; i++) {
      var option=document.createElement('option');
      option.setAttribute('value',db.defconfs[i].id);
      option.innerText=db.defconfs[i].defconfig;
      this.#defconfs.appendChild(option);
    }
    var now=db.images.find((el) => el.now == true);
    now=(now!=undefined)?undefined:db.toolchains.find((el) => el.install == false);
    this.#toolchains.innerHTML='';
    if (db.toolchains.length==0) this.#toolchains.setAttribute('disabled','');
    else this.#toolchains.removeAttribute('disabled');
    for (var i=0; i<db.toolchains.length; i++) {
      var toolchain=db.toolchains[i];
      var option=document.createElement('option');
      option.setAttribute('value',toolchain.id);
      var defconfig=toolchain.defconfig;
      var version=toolchain.version;
      option.innerText=this.#db.defconfs.find((el) => el.id==defconfig).defconfig+' (Toolchain : '+this.#db.versions.find((el) => el.id==version).title+')';
      if (toolchain.install==false) { 
        option.setAttribute('disabled','');
        option.classList.add('compiling');
        if (now!=undefined && now.id==toolchain.id) option.classList.add('now');
      } else option.classList.add('compiled');
      this.#toolchains.appendChild(option);
    }
    this.#speedups.innerHTML='';
    if (db.speedups.length==0) this.#speedups.setAttribute('disabled','');
    else this.#speedups.removeAttribute('disabled');
    for (var i=0; i<db.speedups.length; i++) {
      var speedup=db.speedups[i];
      var option=document.createElement('option');
      option.setAttribute('value',speedup.id);
      option.innerText=speedup.title;
      this.#speedups.appendChild(option);
    }
    this.refresh1();
    this.refresh2();
  }

  refresh1() {
    this.#versionsfortc.innerHTML='';
    if (this.#db.versions.length==0) this.#versionsfortc.setAttribute('disabled','');
    else this.#versionsfortc.removeAttribute('disabled');
    var id=this.#defconfs.selectedOptions[0];
    if (this.#db.versions.length==0 || id==undefined) return;
    id=id.value;
    var now=this.#db.images.find((el) => el.now == true);
    now=(now!=undefined)?undefined:this.#db.toolchains.find((el) => el.install == false);
    for (var i=0;i<this.#db.versions.length; i++) {
      var version=this.#db.versions[i];
      var install=this.#db.toolchains.find((el) => el.defconfig==id && el.version==version.id);
      var option=document.createElement('option');
      option.setAttribute('value',version.id);
      if (version.defconfs.find((el) => el == id) == undefined)
        option.setAttribute('disabled','');
      else if (install!=undefined) {
        option.setAttribute('disabled','');
        option.classList.add(install.install?'compiled':'compiling');
        if (now!=undefined && id==now.defconfig && version.id==now.id) option.classList.add('now');
      }
      option.innerText=version.title;
      this.#versionsfortc.appendChild(option);
    }
  }

  refresh2() {
    this.#versions.innerHTML='';
    this.#versions.setAttribute('disabled','');
    var id=this.#toolchains.selectedOptions[0];
    if (this.#db.versions.length==0 || id==undefined) return;
    id=id.value;
    var now=this.#db.images.find((el) => el.now == true);
    var toolchain=this.#db.toolchains.find((el) => el.id==id)
    var defver=toolchain.version;
    var defconf=toolchain.defconfig;
    for (var i=0;i<this.#db.versions.length; i++) {
      var version=this.#db.versions[i];
      if (version.defconfs.find((el) => el==defconf)==undefined) continue;
      this.#versions.removeAttribute('disabled');
      var option=document.createElement('option');
      option.setAttribute('value',version.id);
      option.innerText=version.title;
      var image=this.#db.images.find((el) => el.version==version.id && el.toolchain == id);
      if (image!=undefined) {
        option.setAttribute('disabled','');
        option.classList.add((image.install)?'compiled':'compiling');
        if (now!=undefined && now.version==version.id && now.toolchain==id) option.classList.add('now');
      }
      if (defver==version.id) option.setAttribute('selected','');
      this.#versions.appendChild(option);
    }
  }

  show() {
    this.#mydiv.style.display='block';
  }

  unshow() {
    this.#mydiv.style.display='';
    document.getElementById('admin_errorAdd').style.visibility='';
  }

  checkAdd() {
    var title=document.getElementById('admin_version').value;
    var flag=this.#db.versions.find((element) => element.title == title)==undefined;
    document.getElementById('admin_errorAdd').style.visibility=flag?'':'visible';
    setTimeout(() => { document.getElementById('admin_errorAdd').style.visibility=''; }, 5000);
    if (flag==false) return false;
    return title;
  }

  checkCompileTC() {
    var idversion=this.#versionsfortc.selectedOptions[0];
    var iddefconf=this.#defconfs.selectedOptions[0];
    if (idversion==undefined || iddefconf==undefined) return false;
    idversion=idversion.value;
    iddefconf=iddefconf.value;
    var flag=this.#db.toolchains.find((el) => el.version==idversion && el.defconf==iddefconf);
    if (flag!=undefined) return false;
    return new Array(idversion, iddefconf); 
  }

  checkCompile() {
    var idversion=this.#versions.selectedOptions[0];
    var idtoolchain=this.#toolchains.selectedOptions[0];
    var idspeedup=this.#speedups.selectedOptions[0];
    if (idversion==undefined || idtoolchain==undefined || idspeedup==undefined) return false;
    idversion=idversion.value;
    idtoolchain=idtoolchain.value;
    idspeedup=idspeedup.value;
    var flag=this.#db.images.find((el) => el.version==idversion && el.toolchain==idtoolchain);
    if (flag!=undefined) return false;
    return new Array(idversion,idtoolchain,idspeedup); 
  }

  show_error() {
    document.getElementById('admin_errorAdd').style.visibility='visible';
  }
}