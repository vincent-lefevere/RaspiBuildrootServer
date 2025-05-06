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
    if (xhttp.readyState === XMLHttpRequest.DONE && xhttp.status === 200) {
      document.body.className='';
      if (xhttp.responseText!='false') spa.updatedpt(JSON.parse(xhttp.responseText));
    }
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
 
 adminAdd(title) {
  var form = new FormData();
  form.append('title',title);
  var xhttp= new XMLHttpRequest();
  xhttp.open('POST','backend2/bradd.php', true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE && xhttp.status === 200) {
      document.body.className='';
      if (xhttp.responseText=='true') this.loadversion();
      else this.admin_show_error();
    }
  }.bind(this.#spa));
  xhttp.send(form);
  document.body.className='wait';
 }

 adminCompileTC(version,defconf) {
  var form = new FormData();
  form.append('version',version);
  form.append('defconf',defconf);
  var xhttp= new XMLHttpRequest();
  xhttp.open('POST','backend2/brtc.php', true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE && xhttp.status === 200) {
      document.body.className='';
      if (xhttp.responseText=='true') this.loadversion();
    }
  }.bind(this.#spa));
  xhttp.send(form);
  document.body.className='wait';
 }

 adminCompile(version,toolchain,speedup) {
  var form = new FormData();
  form.append('version',version);
  form.append('toolchain',toolchain);
  form.append('speedup',speedup);
  var xhttp= new XMLHttpRequest();
  xhttp.open('POST','backend2/brimg.php', true);
  xhttp.addEventListener('readystatechange', function() {
    if (xhttp.readyState === XMLHttpRequest.DONE && xhttp.status === 200) {
      document.body.className='';
      if (xhttp.responseText=='true') this.loadversion();
    }
  }.bind(this.#spa));
  xhttp.send(form);
  document.body.className='wait';
 }
}
