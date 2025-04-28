class MyPrj {
  #id;
  #mydiv;
  #myinput;
  #db;
  #auth;

  constructor(auth) {
    this.#auth=auth;
    this.#mydiv=document.getElementById('projects');
    this.#myinput=document.getElementById('projectsDisplay');
    this.#db=new Object();
    this.#db.projects=new Array();
    this.#db.dpts=new Array();
  }

  getmydb() {
    var list=this.#db.projects;
    for (var i = 0; i < list.length; i++) {
      if (list[i].id==this.#id)
        return(list[i]);
    }
  }

  getidprj() { return this.#id; }
  setidprj(el) { 
    this.#id=el.getElementsByClassName('tag_idprj')[0].innerHTML;
    this.unshow();
  }

  unshow() {
    this.#myinput.checked=false;
  }

  show() {
    this.#id=undefined;
    this.#myinput.checked=true;
  }

  #buildProject(idprj) {
    var prj;
    this.#db.projects.forEach(function(el) { if (el.id==idprj) prj=el; });
    var tmp=document.getElementById('template_project').firstElementChild.cloneNode(true);
    tmp.getElementsByClassName('tag_idprj')[0].innerHTML=idprj;
    tmp.getElementsByClassName('tag_title')[0].innerHTML=prj.title;
    var html='';
    for (var i=0; i<prj.users.length; i++) {
      var el=prj.users[i];
      if (this.#auth.islogin(el.email)) tmp.className='me';
      if (el.in) html+=el.name+'<br/>';
    }
    tmp.getElementsByClassName('tag_mail')[0].innerHTML=html;
    if (prj.power!==false) tmp.classList.add('on');
    return(tmp);
  }

  #updateProjects(list) {
    this.#db.projects=list;
  }

  #updateDepartements(list) {
    this.#db.dpts=list;
    var div=document.getElementById('tag_dpt');
    var tpl=document.getElementById('template_department');
    var seldpt=0;
    div.childNodes.forEach(function (el) {if(el.children[2].checked) seldpt=el.children[0].value;});
    div.innerHTML='';
    for (var i=0; i<list.length; i++) {
      var id=list[i].id;
      var tmp=tpl.cloneNode(true);
      tmp.removeAttribute('id');
      div.appendChild(tmp);
      tmp.firstElementChild.value=id;
      if (id==seldpt) tmp.children[2].checked=true;
      id='dpt'+id;
      var lbl=tmp.getElementsByClassName('tag_dpt_title')[0];
      lbl.innerHTML=list[i].title;
      lbl.setAttribute('for',id);
      tmp.children[2].setAttribute('id',id);
      tmp.children[1].firstElementChild.style.visibility=list[i].projects.length==0?'':'hidden';
      tmp=tmp.children[3];
      for (var j=0; j<list[i].projects.length; j++) tmp.appendChild(this.#buildProject(list[i].projects[j]));
    }
  }

  #updateMyProjets(list) {
    this.#db.myprj=list;
    var div=document.getElementById('tag_my');
    div.innerHTML='';
    for (var i=0;i<list.length;i++) div.appendChild(this.#buildProject(list[i]));
  }

  buildlist(db) {
    if (db.projects!==undefined) this.#updateProjects(db.projects);
    if (db.myprj!==undefined) this.#updateMyProjets(db.myprj);
    if (db.dpts!==undefined) this.#updateDepartements(db.dpts);
  }

  changelogin() {
    document.getElementById('name').innerHTML=this.#auth.getname();
    this.#mydiv.className=this.#auth.getprof()?'prof':'stud';
  }

  addPrj(el) {
    return(el.parentNode.firstElementChild.value);
  }

  supDpt(el) {
    return(el.parentNode.parentNode.firstElementChild.value);
  }
}
