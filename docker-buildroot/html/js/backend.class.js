class MyBackend {
 #spa;

 constructor(spa) {
  this.#spa=spa;
 }

 loadusers(csv) {
  var form = new FormData();
  form.append('upload[file]',csv.files[0]);
  form.append('upload[name]',csv.value);
  form.append('csv',csv.value);
  var xhttp= new XMLHttpRequest();
  xhttp.open("POST","backend2/adduser.php", true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE && xhttp.status === 200) {
      document.body.className='';
    }
  });
  xhttp.send(form);
  document.body.className='wait';
 }

 #modDpt(form,url,spa) {
  var xhttp= new XMLHttpRequest();
  xhttp.open('POST',url, true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE) {
      document.body.className='';
      if (xhttp.status === 200) {
        if (xhttp.responseText!='false') spa.updatedpt(JSON.parse(xhttp.responseText));
      }
    }
  });
  xhttp.addEventListener('error', function() {
    document.body.className='';
  });
  xhttp.send(form);
  document.body.className='wait';
 }

 supDpt(id) {
  var form = new FormData();
  form.append('id',id);
  this.#modDpt(form,'backend2/deldpt.php',this.#spa);
 }

 addDpt(title) {
  var form = new FormData();
  form.append('title',title);
  this.#modDpt(form,'backend2/adddpt.php',this.#spa); 
 }

 grpDpt(id,grp,flag) {
  var form = new FormData();
  form.append('id',id);
  form.append('grp',grp);
  form.append('flag',flag?'1':'0');
  this.#modDpt(form,'backend2/grpdpt.php',this.#spa); 
 }
 loadexample(el,id) {
  var form = new FormData();
  if (el!=undefined) {
   form.append('upload[file]',el.files[0]);
   form.append('upload[name]',el.value);
  }
  form.append('id',id);
  var xhttp= new XMLHttpRequest();
  xhttp.open("POST","backend2/loadexample.php", true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE) {
      document.body.className='';
    }
  });
  xhttp.send(form);
  document.body.className='wait';
 }


 supPrj(id) {
  var form = new FormData();
  form.append('id',id);
  this.#modDpt(form,'backend2/delprj.php',this.#spa);
 }

 addPrj(id,title) {
  var form = new FormData();
  form.append('id',id);
  form.append('title',title);
  this.#modDpt(form,'backend2/addprj.php',this.#spa);
 }

 #updateprj(form,url,spa) {
  var xhttp= new XMLHttpRequest();
  xhttp.open('POST',url, true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE ) {
      document.body.className='';
      if (xhttp.status === 200 && xhttp.responseText!='false') spa.updateprj(JSON.parse(xhttp.responseText));
    }
  });
  xhttp.addEventListener('error', function() {
    document.body.className='';
  });  
  xhttp.send(form);
  document.body.className='wait';
 }

 enroll(email,idprj) { 
  var form = new FormData();
  form.append('login',email);
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/enter.php',this.#spa);
 }
 unenroll(email,idprj) {
  var form = new FormData();
  form.append('login',email);
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/leave.php',this.#spa);
 }
 lock(idprj,lock) {
  var form = new FormData();
  form.append('lock',lock);
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/lock.php',this.#spa);
 }
 expert(idprj,expert) {
  var form = new FormData();
  form.append('expert',expert);
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/expert.php',this.#spa);
 }
 renPrj(idprj,title){
  var form = new FormData();
  form.append('title',title);
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/renameprj.php',this.#spa);
 }
 choiceImage(idprj,image){
  var form = new FormData();
  form.append('image',image);
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/setimage.php',this.#spa);
 }
 startVM(idprj) {
  var form = new FormData();
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/poweron.php',this.#spa);
 }
 stopVM(idprj) {
  var form = new FormData();
  form.append('projet',idprj);
  this.#updateprj(form,'backend2/poweroff.php',this.#spa);
 }
 rollback(idprj,num) {
  var form = new FormData();
  form.append('projet',idprj);
  form.append('num',num);
  this.#updateprj(form,'backend2/rollback.php',this.#spa);
  document.getElementById('gitlog').style.display='none';
 }
 
 #adminBackend(url,form,on_success,on_error) {
  var xhttp= new XMLHttpRequest();
  xhttp.open('POST',url, true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE) {
      document.body.className='';
      if (xhttp.status === 200) {
        if (xhttp.responseText!='false') on_success();
        else on_error();
      }
    }
  }.bind(this.#spa));
  xhttp.addEventListener('error', function() {
    document.body.className='';
  });
  xhttp.send(form);
  document.body.className='wait';
 }

 adminAdd(title) {
  var form = new FormData();
  form.append('title',title);
  this.#adminBackend('backend2/bradd.php',form,this.#spa.loadversion.bind(this.#spa),this.#spa.admin_show_error.bind(this.#spa));
 }

 adminRm(id) {
  var form = new FormData();
  form.append('id',id);
  this.#adminBackend('backend2/brrm.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }

 adminCompileTC(version,defconf) {
  var form = new FormData();
  form.append('version',version);
  form.append('defconf',defconf);
  this.#adminBackend('backend2/brtc.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }

 adminRmTC(id) {
  var form = new FormData();
  form.append('id',id);
  this.#adminBackend('backend2/tcrm.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }

 adminSpeedAdd(title, packages) {
  var form = new FormData();
  form.append('title',title);
  form.append('packages',packages);
  this.#adminBackend('backend2/spadd.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }

 adminSpeedRm(id) {
  var form = new FormData();
  form.append('id',id);
  this.#adminBackend('backend2/sprm.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }

 adminCompile(version,toolchain,speedup) {
  var form = new FormData();
  form.append('version',version);
  form.append('toolchain',toolchain);
  form.append('speedup',speedup);
  this.#adminBackend('backend2/brimg.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }

 adminRmImg(id) {
  var form = new FormData();
  form.append('id',id);
  this.#adminBackend('backend2/brrmimg.php',form,this.#spa.loadversion.bind(this.#spa),function() {});
 }
}
