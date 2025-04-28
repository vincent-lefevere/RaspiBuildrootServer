class graph {

	static list = new Array();

	#tab;
	#maxTime;
	#canvas;
	#ctx;
	#label;

	constructor(id,mt,color,label) {
		var j=graph.list.length;
		graph.list.push(this);

		this.#tab = new Array();
		this.#maxTime = 1000*mt;
		this.#canvas= document.getElementById(id);
		if (this.#canvas!==undefined && this.#canvas.getContext) { 
			this.#ctx=this.#canvas.getContext('2d');
			if (color!==undefined) this.#ctx.strokeStyle=color;
			setInterval( function() { graph.list[j].update(); },this.#maxTime/this.#canvas.width);
		}
		this.#label=document.getElementById(label);
	}

	input(val,time) {
		this.#tab.push({
			"val" : val,
			"time" : time!==undefined?time:Date.now()
		});
		this.#tab.sort(function(a, b) {
			return a.time - b.time;
		});
	 	this.update();
	}

	clear() {
		this.#tab=new Array();
		this.#label.innerHTML='';
		this.#ctx.clearRect(0, 0, this.#canvas.width, this.#canvas.height);
	}

	update() {
		var now=Date.now();
		var i=0;
	 	while (this.#tab.length>(i+1) && this.#tab[i].time<(now-1.1*this.#maxTime)) i++;
	 	this.#tab.splice(0,i);
		if (this.#tab.length<2) return;
		this.#ctx.clearRect(0, 0, this.#canvas.width, this.#canvas.height);
	 	var min=now-this.#maxTime;
		var scale=this.#canvas.width/this.#maxTime;
		this.#ctx.beginPath();
		this.#ctx.moveTo((this.#tab[0].time-min)*scale,this.#canvas.height-this.#tab[0].val);
		for(i=1; i<this.#tab.length;i++)
			this.#ctx.lineTo((this.#tab[i].time-min)*scale,this.#canvas.height-this.#tab[i].val);
		this.#ctx.stroke();
	}

	setLabel(val) { this.#label.innerHTML=val; }
}

class MyGraph {
	#graph1;
	#graph2;
	#graph3;
	#graph4;
	#graph5;

	constructor() {
		this.#graph1=new graph('graph1',7200,'#0000ff','val1');
		this.#graph2=new graph('graph2',7200,'#0000ff','val2');
		this.#graph3=new graph('graph3',7200,'#0000ff','val3');
		this.#graph4=new graph('graph4',7200,'#0000ff','val4');
		this.#graph5=new graph('graph5',7200,'#0000ff','val5');
	}

	update(obj,p){
		if (obj.mem!=undefined && obj.mem>=0) {
			this.#graph1.input(obj.mem,obj.time*1000);
			this.#graph1.setLabel(obj.mem);
		}
		if (obj.swap!=undefined && obj.swap>=0) {
			this.#graph2.input(obj.swap,obj.time*1000);
			this.#graph2.setLabel(obj.swap);
		}
		if (obj.cpu!=undefined && obj.cpu>=0) {
			this.#graph3.input(obj.cpu,obj.time*1000);
			this.#graph3.setLabel(obj.cpu);
		}
		if (obj.id==p && obj.lmem!=undefined && obj.lmem>=0) {
			this.#graph4.input(obj.lmem,obj.time*1000);
			this.#graph4.setLabel(obj.lmem);
		}
		if (obj.id==p && obj.lcpu!=undefined && obj.lcpu>=0) {
			this.#graph5.input(obj.lcpu,obj.time*1000);
			this.#graph5.setLabel(obj.lcpu);
		}
	}

	callback_load(xhr) {
		if (xhr.readyState !== XMLHttpRequest.DONE) return;
	    if (xhr.status !== 200) return;
		var tab=JSON.parse(xhr.responseText);
		var mem,swap,cpu,lmem,lcpu;
		this.#graph1.clear();
		this.#graph2.clear();
		this.#graph3.clear();
		this.#graph4.clear();
		this.#graph5.clear();
		for (var i=0; i<tab.length; i++) {
			var obj = tab[i];
			if (obj.mem!=undefined && obj.mem>=0) this.#graph1.input(mem=obj.mem,obj.time*1000);
			if (obj.swap!=undefined && obj.swap>=0) this.#graph2.input(swap=obj.swap,obj.time*1000);
			if (obj.cpu!=undefined && obj.cpu>=0) this.#graph3.input(cpu=obj.cpu,obj.time*1000);
			if (obj.lmem!=undefined && obj.lmem>=0) this.#graph4.input(lmem=obj.lmem,obj.time*1000);
			if (obj.lcpu!=undefined && obj.lcpu>=0) this.#graph5.input(lcpu=obj.lcpu,obj.time*1000);
		}
		if (mem!=undefined) this.#graph1.setLabel(mem);
		if (swap!=undefined) this.#graph2.setLabel(swap);
		if (cpu!=undefined) this.#graph3.setLabel(cpu);
		if (lmem!=undefined) this.#graph4.setLabel(lmem);
		if (lcpu!=undefined) this.#graph5.setLabel(lcpu);
	}

	load(p) {
		var xhr= new XMLHttpRequest();
		xhr.open("POST","backend2/graph.php", true);
		var form = new FormData();
		form.append('project',p);
		var me=this;
		xhr.addEventListener('readystatechange', function() { me.callback_load(xhr); });
		xhr.send(form);
	}
}
