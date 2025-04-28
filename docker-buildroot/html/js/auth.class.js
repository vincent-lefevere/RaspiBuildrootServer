class MyAuth {
  #spa;
  #mydiv;
  #myinput;
  #name;
  #prof;
  #login;

  constructor(obj) {
    this.#spa=obj;
    this.#mydiv=document.getElementById('ident');
    this.#myinput=document.getElementById('identDisplay');
    this.checkchangepasswd();
  }

  login() {
    var xhr= new XMLHttpRequest();
    xhr.open("POST","backend2/login.php", true);
    var form = new FormData();
    form.append('login',this.#login=document.getElementById('login').value);
    form.append('pwd',document.getElementById('passwd').value);
    var me=this;
    xhr.addEventListener('readystatechange', function() { me.callback(xhr); });
    xhr.send(form);
    document.getElementById('passwd').value='';
    document.body.className='wait';
  }

  callback(xhr) {
    if (xhr.readyState !== XMLHttpRequest.DONE) return;
    if (xhr.status !== 200) return;
    document.body.className='';
    if (xhr.responseText=='false') {
      this.#mydiv.getElementsByTagName('p')[0].style.display='';
    } else {
      this.#mydiv.getElementsByTagName('p')[0].style.display='none';
      var db=JSON.parse(xhr.responseText);
      this.#name=db.name;
      this.#prof=db.prof;
      this.#spa.callbacklogin(db);
      var me=this;
      setTimeout(function () { me.ping(); }, 600000);
      this.#myinput.checked=false;
    }
  }

  #checkchangepasswd() {
    if (document.getElementById('newpasswd').value=="") return(false);
    if (document.getElementById('newpasswd').value!=document.getElementById('newpasswd2').value) return(false);
    if (document.getElementById('newpasswd').value==document.getElementById('passwd').value) return(false);
    if (document.getElementById('login').value=="") return(false);
    if (document.getElementById('passwd').value=="") return(false);
    return(true);
  }

  checkchangepasswd() {
    var l1=document.getElementsByClassName('login');
    var l2=document.getElementsByClassName('changepasswd');
    var b=this.#checkchangepasswd();
    l1[0].disabled=l1[1].disabled=b;
    l2[0].disabled=l2[1].disabled=!b
  }

  changepasswd() {
    var xhr= new XMLHttpRequest();
    xhr.open("POST","backend2/changepasswd.php", true);
    var form = new FormData();
    form.append('login',this.#login=document.getElementById('login').value);
    form.append('pwd',document.getElementById('passwd').value);
    form.append('newpwd',document.getElementById('newpasswd').value);
    var me=this;
    xhr.addEventListener('readystatechange', function() { me.changepasswdcallback(xhr); });
    xhr.send(form);
    document.getElementById('passwd').value='';
    document.body.className='wait';
    this.checkchangepasswd();
  }

  changepasswdcallback(xhr) {
    if (xhr.readyState !== XMLHttpRequest.DONE) return;
    if (xhr.status !== 200) return;
    document.body.className='';
    if (xhr.responseText=='false') {
      this.#mydiv.getElementsByTagName('p')[0].style.display='';
    } else {
      this.#mydiv.getElementsByTagName('p')[0].style.display='none';
      document.getElementById('passwd').value=document.getElementById('newpasswd').value;
      document.getElementById('newpasswd').value=document.getElementById('newpasswd2').value='';
    }
  }

  ping() {
    var xhr= new XMLHttpRequest();
    xhr.open("GET","backend2/ping.php", true);
    var me=this;
    xhr.addEventListener('readystatechange', function() { me.pingcallback(xhr); });
    xhr.addEventListener('timeout', function() { me.pingsettimeout(30000); });
    xhr.addEventListener('error', function() { me.pingsettimeout(30000); });
    xhr.send(null);
  }

  pingsettimeout(timeout) {
    var me=this;
    setTimeout(function () { me.ping(); }, timeout);
  }

  pingcallback(xhr) {
    if (xhr.readyState !== XMLHttpRequest.DONE) return;
    var me=this;
//    if (xhr.status == 200 && xhr.responseText == 'true') setTimeout(function () { me.ping(); }, 600000);
    if (xhr.status == 200 && xhr.responseText == 'true') this.pingsettimeout(600000);
    else this.logout();
  }

  logout() {
    this.#myinput.checked=true;
  }

  islogin(login) {
    return (login==document.getElementById('login').value);
  }

  isnew() { return (this.#login!=document.getElementById('login').value)}

  getlogin() { return(document.getElementById('login').value); }
  getname() { return(this.#name); }
  getprof() { return(this.#prof); }
}
