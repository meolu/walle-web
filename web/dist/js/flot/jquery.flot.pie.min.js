/* Flot plugin for rendering pie charts.

Copyright (c) 2007-2013 IOLA and Ole Laursen.
Licensed under the MIT license.

The plugin assumes that each series has a single data value, and that each
value is a positive integer or zero.  Negative numbers don't make sense for a
pie chart, and have unpredictable results.  The values do NOT need to be
passed in as percentages; the plugin will calculate the total and per-slice
percentages internally.

* Created by Brian Medendorp

* Updated with contributions from btburnett3, Anthony Aragues and Xavi Ivars

The plugin supports these options:

	series: {
		pie: {
			show: true/false
			radius: 0-1 for percentage of fullsize, or a specified pixel length, or 'auto'
			innerRadius: 0-1 for percentage of fullsize or a specified pixel length, for creating a donut effect
			startAngle: 0-2 factor of PI used for starting angle (in radians) i.e 3/2 starts at the top, 0 and 2 have the same result
			tilt: 0-1 for percentage to tilt the pie, where 1 is no tilt, and 0 is completely flat (nothing will show)
			offset: {
				top: integer value to move the pie up or down
				left: integer value to move the pie left or right, or 'auto'
			},
			stroke: {
				color: any hexidecimal color value (other formats may or may not work, so best to stick with something like '#FFF')
				width: integer pixel width of the stroke
			},
			label: {
				show: true/false, or 'auto'
				formatter:  a user-defined function that modifies the text/style of the label text
				radius: 0-1 for percentage of fullsize, or a specified pixel length
				background: {
					color: any hexidecimal color value (other formats may or may not work, so best to stick with something like '#000')
					opacity: 0-1
				},
				threshold: 0-1 for the percentage value at which to hide labels (if they're too small)
			},
			combine: {
				threshold: 0-1 for the percentage value at which to combine slices (if they're too small)
				color: any hexidecimal color value (other formats may or may not work, so best to stick with something like '#CCC'), if null, the plugin will automatically use the color of the first slice to be combined
				label: any text value of what the combined slice should be labeled
			}
			highlight: {
				opacity: 0-1
			}
		}
	}

More detail and specific examples can be found in the included HTML file.

*/(function(e){function r(r){function p(t,n,r){l||(l=!0,s=t.getCanvas(),o=e(s).parent(),i=t.getOptions(),t.setData(d(t.getData())))}function d(t){var n=0,r=0,s=0,o=i.series.pie.combine.color,u=[];for(var a=0;a<t.length;++a){var f=t[a].data;e.isArray(f)&&f.length==1&&(f=f[0]),e.isArray(f)?!isNaN(parseFloat(f[1]))&&isFinite(f[1])?f[1]=+f[1]:f[1]=0:!isNaN(parseFloat(f))&&isFinite(f)?f=[1,+f]:f=[1,0],t[a].data=[f]}for(var a=0;a<t.length;++a)n+=t[a].data[0][1];for(var a=0;a<t.length;++a){var f=t[a].data[0][1];f/n<=i.series.pie.combine.threshold&&(r+=f,s++,o||(o=t[a].color))}for(var a=0;a<t.length;++a){var f=t[a].data[0][1];(s<2||f/n>i.series.pie.combine.threshold)&&u.push({data:[[1,f]],color:t[a].color,label:t[a].label,angle:f*Math.PI*2/n,percent:f/(n/100)})}return s>1&&u.push({data:[[1,r]],color:o,label:i.series.pie.combine.label,angle:r*Math.PI*2/n,percent:r/(n/100)}),u}function v(r,s){function y(){c.clearRect(0,0,h,p),o.children().filter(".pieLabel, .pieLabelBackground").remove()}function b(){var e=i.series.pie.shadow.left,t=i.series.pie.shadow.top,n=10,r=i.series.pie.shadow.alpha,s=i.series.pie.radius>1?i.series.pie.radius:u*i.series.pie.radius;if(s>=h/2-e||s*i.series.pie.tilt>=p/2-t||s<=n)return;c.save(),c.translate(e,t),c.globalAlpha=r,c.fillStyle="#000",c.translate(a,f),c.scale(1,i.series.pie.tilt);for(var o=1;o<=n;o++)c.beginPath(),c.arc(0,0,s,0,Math.PI*2,!1),c.fill(),s-=o;c.restore()}function w(){function l(e,t,i){if(e<=0||isNaN(e))return;i?c.fillStyle=t:(c.strokeStyle=t,c.lineJoin="round"),c.beginPath(),Math.abs(e-Math.PI*2)>1e-9&&c.moveTo(0,0),c.arc(0,0,n,r,r+e/2,!1),c.arc(0,0,n,r+e/2,r+e,!1),c.closePath(),r+=e,i?c.fill():c.stroke()}function d(){function l(t,n,s){if(t.data[0][1]==0)return!0;var u=i.legend.labelFormatter,l,c=i.series.pie.label.formatter;u?l=u(t.label,t):l=t.label,c&&(l=c(l,t));var d=(n+t.angle+n)/2,v=a+Math.round(Math.cos(d)*r),m=f+Math.round(Math.sin(d)*r)*i.series.pie.tilt,g="<span class='pieLabel' id='pieLabel"+s+"' style='position:absolute;top:"+m+"px;left:"+v+"px;'>"+l+"</span>";o.append(g);var y=o.children("#pieLabel"+s),b=m-y.height()/2,w=v-y.width()/2;y.css("top",b),y.css("left",w);if(0-b>0||0-w>0||p-(b+y.height())<0||h-(w+y.width())<0)return!1;if(i.series.pie.label.background.opacity!=0){var E=i.series.pie.label.background.color;E==null&&(E=t.color);var S="top:"+b+"px;left:"+w+"px;";e("<div class='pieLabelBackground' style='position:absolute;width:"+y.width()+"px;height:"+y.height()+"px;"+S+"background-color:"+E+";'></div>").css("opacity",i.series.pie.label.background.opacity).insertBefore(y)}return!0}var n=t,r=i.series.pie.label.radius>1?i.series.pie.label.radius:u*i.series.pie.label.radius;for(var s=0;s<v.length;++s){if(v[s].percent>=i.series.pie.label.threshold*100&&!l(v[s],n,s))return!1;n+=v[s].angle}return!0}var t=Math.PI*i.series.pie.startAngle,n=i.series.pie.radius>1?i.series.pie.radius:u*i.series.pie.radius;c.save(),c.translate(a,f),c.scale(1,i.series.pie.tilt),c.save();var r=t;for(var s=0;s<v.length;++s)v[s].startAngle=r,l(v[s].angle,v[s].color,!0);c.restore();if(i.series.pie.stroke.width>0){c.save(),c.lineWidth=i.series.pie.stroke.width,r=t;for(var s=0;s<v.length;++s)l(v[s].angle,i.series.pie.stroke.color,!1);c.restore()}return m(c),c.restore(),i.series.pie.label.show?d():!0}if(!o)return;var h=r.getPlaceholder().width(),p=r.getPlaceholder().height(),d=o.children().filter(".legend").children().width()||0;c=s,l=!1,u=Math.min(h,p/i.series.pie.tilt)/2,f=p/2+i.series.pie.offset.top,a=h/2,i.series.pie.offset.left=="auto"?i.legend.position.match("w")?a+=d/2:a-=d/2:a+=i.series.pie.offset.left,a<u?a=u:a>h-u&&(a=h-u);var v=r.getData(),g=0;do g>0&&(u*=n),g+=1,y(),i.series.pie.tilt<=.8&&b();while(!w()&&g<t);g>=t&&(y(),o.prepend("<div class='error'>Could not draw pie with labels contained inside canvas</div>")),r.setSeries&&r.insertLegend&&(r.setSeries(v),r.insertLegend())}function m(e){if(i.series.pie.innerRadius>0){e.save();var t=i.series.pie.innerRadius>1?i.series.pie.innerRadius:u*i.series.pie.innerRadius;e.globalCompositeOperation="destination-out",e.beginPath(),e.fillStyle=i.series.pie.stroke.color,e.arc(0,0,t,0,Math.PI*2,!1),e.fill(),e.closePath(),e.restore(),e.save(),e.beginPath(),e.strokeStyle=i.series.pie.stroke.color,e.arc(0,0,t,0,Math.PI*2,!1),e.stroke(),e.closePath(),e.restore()}}function g(e,t){for(var n=!1,r=-1,i=e.length,s=i-1;++r<i;s=r)(e[r][1]<=t[1]&&t[1]<e[s][1]||e[s][1]<=t[1]&&t[1]<e[r][1])&&t[0]<(e[s][0]-e[r][0])*(t[1]-e[r][1])/(e[s][1]-e[r][1])+e[r][0]&&(n=!n);return n}function y(e,t){var n=r.getData(),i=r.getOptions(),s=i.series.pie.radius>1?i.series.pie.radius:u*i.series.pie.radius,o,l;for(var h=0;h<n.length;++h){var p=n[h];if(p.pie.show){c.save(),c.beginPath(),c.moveTo(0,0),c.arc(0,0,s,p.startAngle,p.startAngle+p.angle/2,!1),c.arc(0,0,s,p.startAngle+p.angle/2,p.startAngle+p.angle,!1),c.closePath(),o=e-a,l=t-f;if(c.isPointInPath){if(c.isPointInPath(e-a,t-f))return c.restore(),{datapoint:[p.percent,p.data],dataIndex:0,series:p,seriesIndex:h}}else{var d=s*Math.cos(p.startAngle),v=s*Math.sin(p.startAngle),m=s*Math.cos(p.startAngle+p.angle/4),y=s*Math.sin(p.startAngle+p.angle/4),b=s*Math.cos(p.startAngle+p.angle/2),w=s*Math.sin(p.startAngle+p.angle/2),E=s*Math.cos(p.startAngle+p.angle/1.5),S=s*Math.sin(p.startAngle+p.angle/1.5),x=s*Math.cos(p.startAngle+p.angle),T=s*Math.sin(p.startAngle+p.angle),N=[[0,0],[d,v],[m,y],[b,w],[E,S],[x,T]],C=[o,l];if(g(N,C))return c.restore(),{datapoint:[p.percent,p.data],dataIndex:0,series:p,seriesIndex:h}}c.restore()}}return null}function b(e){E("plothover",e)}function w(e){E("plotclick",e)}function E(e,t){var n=r.offset(),s=parseInt(t.pageX-n.left),u=parseInt(t.pageY-n.top),a=y(s,u);if(i.grid.autoHighlight)for(var f=0;f<h.length;++f){var l=h[f];l.auto==e&&(!a||l.series!=a.series)&&x(l.series)}a&&S(a.series,e);var c={pageX:t.pageX,pageY:t.pageY};o.trigger(e,[c,a])}function S(e,t){var n=T(e);n==-1?(h.push({series:e,auto:t}),r.triggerRedrawOverlay()):t||(h[n].auto=!1)}function x(e){e==null&&(h=[],r.triggerRedrawOverlay());var t=T(e);t!=-1&&(h.splice(t,1),r.triggerRedrawOverlay())}function T(e){for(var t=0;t<h.length;++t){var n=h[t];if(n.series==e)return t}return-1}function N(e,t){function s(e){if(e.angle<=0||isNaN(e.angle))return;t.fillStyle="rgba(255, 255, 255, "+n.series.pie.highlight.opacity+")",t.beginPath(),Math.abs(e.angle-Math.PI*2)>1e-9&&t.moveTo(0,0),t.arc(0,0,r,e.startAngle,e.startAngle+e.angle/2,!1),t.arc(0,0,r,e.startAngle+e.angle/2,e.startAngle+e.angle,!1),t.closePath(),t.fill()}var n=e.getOptions(),r=n.series.pie.radius>1?n.series.pie.radius:u*n.series.pie.radius;t.save(),t.translate(a,f),t.scale(1,n.series.pie.tilt);for(var i=0;i<h.length;++i)s(h[i].series);m(t),t.restore()}var s=null,o=null,u=null,a=null,f=null,l=!1,c=null,h=[];r.hooks.processOptions.push(function(e,t){t.series.pie.show&&(t.grid.show=!1,t.series.pie.label.show=="auto"&&(t.legend.show?t.series.pie.label.show=!1:t.series.pie.label.show=!0),t.series.pie.radius=="auto"&&(t.series.pie.label.show?t.series.pie.radius=.75:t.series.pie.radius=1),t.series.pie.tilt>1?t.series.pie.tilt=1:t.series.pie.tilt<0&&(t.series.pie.tilt=0))}),r.hooks.bindEvents.push(function(e,t){var n=e.getOptions();n.series.pie.show&&(n.grid.hoverable&&t.unbind("mousemove").mousemove(b),n.grid.clickable&&t.unbind("click").click(w))}),r.hooks.processDatapoints.push(function(e,t,n,r){var i=e.getOptions();i.series.pie.show&&p(e,t,n,r)}),r.hooks.drawOverlay.push(function(e,t){var n=e.getOptions();n.series.pie.show&&N(e,t)}),r.hooks.draw.push(function(e,t){var n=e.getOptions();n.series.pie.show&&v(e,t)})}var t=10,n=.95,i={series:{pie:{show:!1,radius:"auto",innerRadius:0,startAngle:1.5,tilt:1,shadow:{left:5,top:15,alpha:.02},offset:{top:0,left:"auto"},stroke:{color:"#fff",width:1},label:{show:"auto",formatter:function(e,t){return"<div style='font-size:x-small;text-align:center;padding:2px;color:"+t.color+";'>"+e+"<br/>"+Math.round(t.percent)+"%</div>"},radius:1,background:{color:null,opacity:0},threshold:0},combine:{threshold:-1,color:null,label:"Other"},highlight:{opacity:.5}}}};e.plot.plugins.push({init:r,options:i,name:"pie",version:"1.1"})})(jQuery);