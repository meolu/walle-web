/* Flot plugin for automatically redrawing plots as the placeholder resizes.

Copyright (c) 2007-2013 IOLA and Ole Laursen.
Licensed under the MIT license.

It works by listening for changes on the placeholder div (through the jQuery
resize event plugin) - if the size changes, it will redraw the plot.

There are no options. If you need to disable the plugin for some plots, you
can just fix the size of their placeholders.

*//* Inline dependency:
 * jQuery resize event - v1.1 - 3/14/2010
 * http://benalman.com/projects/jquery-resize-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */(function(e,t,n){function c(){s=t[o](function(){r.each(function(){var t=e(this),n=t.width(),r=t.height(),i=e.data(this,a);(n!==i.w||r!==i.h)&&t.trigger(u,[i.w=n,i.h=r])}),c()},i[f])}var r=e([]),i=e.resize=e.extend(e.resize,{}),s,o="setTimeout",u="resize",a=u+"-special-event",f="delay",l="throttleWindow";i[f]=250,i[l]=!0,e.event.special[u]={setup:function(){if(!i[l]&&this[o])return!1;var t=e(this);r=r.add(t),e.data(this,a,{w:t.width(),h:t.height()}),r.length===1&&c()},teardown:function(){if(!i[l]&&this[o])return!1;var t=e(this);r=r.not(t),t.removeData(a),r.length||clearTimeout(s)},add:function(t){function s(t,i,s){var o=e(this),u=e.data(this,a);u.w=i!==n?i:o.width(),u.h=s!==n?s:o.height(),r.apply(this,arguments)}if(!i[l]&&this[o])return!1;var r;if(e.isFunction(t))return r=t,s;r=t.handler,t.handler=s}}})(jQuery,this),function(e){function n(e){function t(){var t=e.getPlaceholder();if(t.width()==0||t.height()==0)return;e.resize(),e.setupGrid(),e.draw()}function n(e,n){e.getPlaceholder().resize(t)}function r(e,n){e.getPlaceholder().unbind("resize",t)}e.hooks.bindEvents.push(n),e.hooks.shutdown.push(r)}var t={};e.plot.plugins.push({init:n,options:t,name:"resize",version:"1.0"})}(jQuery);