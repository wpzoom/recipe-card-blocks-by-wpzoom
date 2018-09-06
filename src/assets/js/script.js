(function($){
	'use scrict';

	$(document).ready(function () {

		$(".wp-block-wpzoom-recipe-card-block-ingredients .ingredients-list li").click(function(){
		    $(this).toggleClass("ticked");
		});

		$(".wpzoom-recipe-card-print-link .btn-print-link").click(function(e){
			var block = $(this).attr('href'),
				id = $(block).attr('id');

			$(block).print({
				globalStyles: true,
	        	mediaPrint: false,
	        	noPrintSelector: ".no-print",
	        	stylesheet: wpzoomRecipeCard.pluginURL + '/src/assets/css/recipe-print.css',
	        	iframe: true,
	        	doctype: '<!doctype html>'
			});

			return false;
		});

	});

})(jQuery);


/* @license 
 * jQuery.print, version 1.5.1
 *  (c) Sathvik Ponangi, Doers' Guild
 * Licence: CC-By (http://creativecommons.org/licenses/by/3.0/)
 *--------------------------------------------------------------------------*/
!function(e){"use strict";function t(t){var n=e("");try{n=e(t).clone()}catch(r){n=e("<span />").html(t)}return n}function n(t,n,r){var o=e.Deferred();try{var i=(t=t.contentWindow||t.contentDocument||t).document||t.contentDocument||t;r.doctype&&i.write(r.doctype),i.write(n),i.close();var a=!1,c=function(){if(!a){t.focus();try{t.document.execCommand("print",!1,null)||t.print(),e("body").focus()}catch(e){t.print()}t.close(),a=!0,o.resolve()}};e(t).on("load",c),setTimeout(c,r.timeout)}catch(e){o.reject(e)}return o}function r(e,t){return n(window.open(),e,t).always(function(){try{t.deferred.resolve()}catch(e){console.warn("Error notifying deferred",e)}})}function o(e){return!!("object"==typeof Node?e instanceof Node:e&&"object"==typeof e&&"number"==typeof e.nodeType&&"string"==typeof e.nodeName)}e.print=e.fn.print=function(){var i,a,c=this;c instanceof e&&(c=c.get(0)),o(c)?(a=e(c),arguments.length>0&&(i=arguments[0])):arguments.length>0?o((a=e(arguments[0]))[0])?arguments.length>1&&(i=arguments[1]):(i=arguments[0],a=e("html")):a=e("html");var l={globalStyles:!0,mediaPrint:!1,stylesheet:null,noPrintSelector:".no-print",iframe:!0,append:null,prepend:null,manuallyCopyFormValues:!0,deferred:e.Deferred(),timeout:750,title:null,doctype:"<!doctype html>"};i=e.extend({},l,i||{});var d=e("");i.globalStyles?d=e("style, link, meta, base, title"):i.mediaPrint&&(d=e("link[media=print]")),i.stylesheet&&(d=e.merge(d,e('<link rel="stylesheet" href="'+i.stylesheet+'">')));var f=a.clone();if((f=e("<span/>").append(f)).find(i.noPrintSelector).remove(),f.append(d.clone()),i.title){var s=e("title",f);0===s.length&&(s=e("<title />"),f.append(s)),s.text(i.title)}f.append(t(i.append)),f.prepend(t(i.prepend)),i.manuallyCopyFormValues&&(f.find("input").each(function(){var t=e(this);t.is("[type='radio']")||t.is("[type='checkbox']")?t.prop("checked")&&t.attr("checked","checked"):t.attr("value",t.val())}),f.find("select").each(function(){e(this).find(":selected").attr("selected","selected")}),f.find("textarea").each(function(){var t=e(this);t.text(t.val())}));var p,u,m,y,h=f.html();try{i.deferred.notify("generated_markup",h,f)}catch(e){console.warn("Error notifying deferred",e)}if(f.remove(),i.iframe)try{p=h,m=e((u=i).iframe+""),0===(y=m.length)&&(m=e('<iframe height="0" width="0" border="0" wmode="Opaque"/>').prependTo("body").css({position:"absolute",top:-999,left:-999})),n(m.get(0),p,u).done(function(){setTimeout(function(){0===y&&m.remove()},1e3)}).fail(function(e){console.error("Failed to print from iframe",e),r(p,u)}).always(function(){try{u.deferred.resolve()}catch(e){console.warn("Error notifying deferred",e)}})}catch(e){console.error("Failed to print from iframe",e.stack,e.message),r(h,i)}else r(h,i);return this}}(jQuery);
