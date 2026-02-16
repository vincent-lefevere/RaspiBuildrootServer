class MyAdmin {
  #mydiv;
  #defconfs;
  #versions;
  #versionsfortc;
  #toolchains;
  #speedups;
  #speedup;
  #images;
  #image;
  #buttonCreateTC;
  #buttonCreateIMG;
  #db;

  constructor() {
    this.#mydiv=document.getElementById('admin');
    this.#defconfs=document.getElementById('admin_defconfs');
    this.#versions=document.getElementById('admin_versions');
    this.#versionsfortc=document.getElementById('admin_versions_for_tc');
    this.#speedups=document.getElementById('speedup_list');
    this.#speedup=document.getElementById('speedup_title');
    this.#images=document.getElementById('tag_img');
    this.#image=document.getElementById('template_image');
    this.#toolchains=document.getElementById('admin_toolchains');
    this.#buttonCreateTC=document.getElementById('buttonCreateTC');
    this.#buttonCreateIMG=document.getElementById('buttonCreateIMG');
  }

  init(db) {
    if (db==undefined) db={versions:[],toolchains:[],defconfs:[],speedups:[],images:[]};
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
    var flag=false;
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
      } else {
        option.classList.add('compiled');
        flag=true;
      }
      this.#toolchains.appendChild(option);
    }
    
    var tab=Array.from(this.#buttonCreateIMG.children);
    if (flag) tab.forEach((el) => el.removeAttribute('disabled'));
    else tab.forEach((el) => el.setAttribute('disabled',''));

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
    this.refresh3();
    this.refresh4();
    this.refresh5();
    this.refresh6();
  }

  #hideSuprOrNot(node,flag) {
    Array.from(node.getElementsByClassName('supr')).forEach((el) => el.style.visibility=(flag?'hidden':''));
  }

  refresh1() {
    this.#versionsfortc.innerHTML='';
    var flag=this.#db.versions.length!=0;
    this.#hideSuprOrNot(this.#versionsfortc.parentNode,flag);
    if (flag) this.#versionsfortc.removeAttribute('disabled');
    else this.#versionsfortc.setAttribute('disabled','');
    var id=this.#defconfs.selectedOptions[0];
    // if (this.#db.versions.length==0 || id==undefined) return;
    id=(id==undefined)?'':id.value;
    var now=this.#db.images.find((el) => el.now == true);
    now=(now!=undefined)?undefined:this.#db.toolchains.find((el) => el.install == false);
    if (flag) {
      flag=false;
      for (var i=0; i<this.#db.versions.length; i++) { 
        var version=this.#db.versions[i];
        var install=this.#db.toolchains.find((el) => el.defconfig==id && el.version==version.id);
        var option=document.createElement('option');
        option.setAttribute('value',version.id);
        if (version.defconfs.find((el) => el == id) == undefined) {
          option.setAttribute('disabled','');
        } else if (install!=undefined) {
          option.setAttribute('disabled','');
          option.classList.add(install.install?'compiled':'compiling');
          if (now!=undefined && id==now.defconfig && version.id==now.id) option.classList.add('now');
        } else flag=true;
        option.innerText=version.title;
        this.#versionsfortc.appendChild(option);
      }
    }
    var tab=Array.from(this.#buttonCreateTC.children);
    if (flag) tab.forEach((el) => el.removeAttribute('disabled'));
    else tab.forEach((el) => el.setAttribute('disabled',''));
  }

  refresh2() {
    this.#versions.innerHTML='';
    this.#versions.setAttribute('disabled','');
    var id=this.#toolchains.selectedOptions[0];
    if (this.#db.versions.length==0 || id==undefined) return this.#hideSuprOrNot(this.#toolchains.parentNode,true);
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
    this.#hideSuprOrNot(this.#toolchains.parentNode,this.#db.images.find((el) => el.toolchain==id)!=undefined);
  }

  refresh3() {
    this.#hideSuprOrNot(this.#speedups.parentNode,this.#speedups.value<3);
  }

  refresh4() {
    var flag=false;
    var title=this.#speedup.value.trim();
    if (title.length==0) flag=true;
    else flag=this.#db.speedups.find((element) => element.title == title)!=undefined;
    Array.from(this.#speedup.parentNode.parentNode.lastElementChild.children).forEach((el) => el.disabled=flag);
  }

  refresh5() {
    var idversion=this.#versionsfortc.value;
    var flag=(idversion=='') || (this.#db.toolchains.find((el) => el.version==idversion)!=undefined) || (this.#db.images.find((el) => el.version==idversion)!=undefined) ;
    this.#hideSuprOrNot(this.#versionsfortc.parentNode,flag);
  }

  refresh6() {
    while (this.#images.children.length>1) this.#images.removeChild(this.#images.lastChild);
    var one=false;
    var li=this.#db.images.find((el) => el.install)
    if (li!=undefined && li.length==1) one=true;
    for (var i=0; i<this.#db.images.length; i++) {
      var image=this.#db.images[i];
      var toolchain=this.#db.toolchains.find((el) => el.id==image.toolchain);
      var div=this.#image.cloneNode(true);
      div.removeAttribute('id');
      var tmp='Buildroot '+this.#db.versions.find((el) => el.id == image.version).title;
      tmp+=' ('+this.#db.defconfs.find((el) => el.id == image.defconf).defconfig;
      tmp+=' - Toolchain '+this.#db.versions.find((el) => el.id == toolchain.version).title;
      tmp+=')'
      div.children[0].value=image.id;
      div.children[1].value=tmp;
      if (! image.install) { 
        div.children[1].disabled=true;
        div.children[2].style.visibility='hidden';
      }
      if (one) div.children[2].style.visibility='hidden';
      this.#images.appendChild(div);
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