class MyLang {
  #lang;

  constructor() {
    this.#lang=document.getElementById('lang');
    this.langue();
  }

  langue() {
    var lang=this.#lang.value;
    new Array('fr','en').forEach(function(el) { 
      var display=(lang!=el)?'none':'';
      var list=document.body.getElementsByClassName(el);
      for(var i=0;i<list.length;i++) list[i].style.display=display;
    });
  }
}
