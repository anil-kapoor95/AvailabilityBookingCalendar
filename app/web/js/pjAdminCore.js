/* CSRF: attach token to all same-origin admin AJAX + form submits. */
function pjCsrfToken(){var m=document.querySelector('meta[name="csrf-token"]');return m?m.getAttribute("content"):"";}
 
(function(){
	if (typeof jQuery === 'undefined') { return; }
	jQuery.ajaxPrefilter(function (options) {
		if (options.crossDomain) { return; }
		var t = pjCsrfToken(); if (!t) { return; }
		options.headers = options.headers || {};
		options.headers['X-CSRF-Token'] = t;
	});
 
	function pjAttachCsrf(form) {
		var m = (form.getAttribute('method') || 'get').toLowerCase();
		if (m !== 'post') { return; }
		var t = pjCsrfToken();
 
		if (t && !form.querySelector('input[name="csrf_token"]')) {
			var i = document.createElement('input');
			i.type = 'hidden'; i.name = 'csrf_token'; i.value = t;
			form.appendChild(i);
		}
	}
 
	jQuery(document).on('submit', 'form', function () { pjAttachCsrf(this); });
 
	/* jQuery Validate (and other code) call the native HTMLFormElement.submit(),
	   which does NOT fire the delegated 'submit' handler above. Wrap it so the
	   CSRF token is still injected on programmatic submits. */
 
	if (window.HTMLFormElement && HTMLFormElement.prototype && !HTMLFormElement.prototype.__pjCsrfWrapped) {
		var pjNativeSubmit = HTMLFormElement.prototype.submit;
 
		HTMLFormElement.prototype.submit = function () {
			try { pjAttachCsrf(this); } catch (e) {}
			return pjNativeSubmit.apply(this, arguments);
		};
 
		HTMLFormElement.prototype.__pjCsrfWrapped = true;
	}
})();

var abApp = abApp || {};
var jQuery = jQuery || $.noConflict();
(function ($, undefined) {
	"use strict";
	$(function () {
		$(".pj-table tbody").on("mouseenter", "tr", function () {
			$(this).addClass("pj-table-row-hover");
		}).on("mouseleave", "tr", function () {
			$(this).removeClass("pj-table-row-hover");
		})
		;
		$(".pj-button").hover(
			function () {
				$(this).addClass("pj-button-hover");
			}, 
			function () {
				$(this).removeClass("pj-button-hover");
			}
		);
		$(".pj-checkbox").hover(
			function () {
				$(this).addClass("pj-checkbox-hover");
			}, 
			function () {
				$(this).removeClass("pj-checkbox-hover");
			}
		);
		$("#content").on("click", ".notice-close", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).closest(".notice-box").fadeOut();
			return false;
		});
		
		$("#leftmenu").on("change", ".setCalendarId", function () {
			var cid = $("option:selected", this).val();
			if (cid !== "" && cid.length > 0) {
				var $this = $(this);
				window.location.href = ["index.php?controller=pjAdmin&action=pjActionRedirect&nextController=", $this.data("controller"), "&nextAction=", $this.data("action"), "&calendar_id=", cid, "&nextParams=", encodeURIComponent("id=" + cid)].join("");
			}
		});
		
		$("html").on("click", ".pjTsCheckAssignCalendar", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var href = $(this).attr('href');
			$.get("index.php?controller=pjAdminCalendars&action=pjActionCheckAssignCalendar").done(function (data) {
				if(data.status == 'OK')
				{
					window.location.href = href;	
				}else{
					swal({
		    			title: "",
						text: data.text,
						type: "warning",
						confirmButtonColor: "#DD6B55",
						confirmButtonText: data.btnOK,
						closeOnConfirm: false,
						showLoaderOnConfirm: false
					}, function () {
						swal.close();
					});
				}
			});
		});
	});
})(jQuery);
/* Input hygiene: no leading/trailing/duplicate whitespace where it is not valid (mirrors app/config/input.inc.php). */
(function(){
	var WS = /^[\s\u00A0\u200B-\u200D\uFEFF]+|[\s\u00A0\u200B-\u200D\uFEFF]+$/g;
	var INNER = /[\s\u00A0]+/g;
	function scope(el){ return el.closest && (el.closest('form') || el.closest('.abForm')); }
	function applies(el){
		if (!el || !el.tagName || !el.name) { return false; }
		var t = (el.type || '').toLowerCase(), tag = el.tagName.toLowerCase();
		if (tag === 'textarea') { return true; }
		if (tag !== 'input') { return false; }
		if (/^(password|hidden|file|checkbox|radio|submit|button|image|reset|range|color)$/.test(t)) { return false; }
		if (/pass|csrf|token|cc_|captcha/i.test(el.name)) { return false; }
		return true;
	}
	function kind(el){
		var n = (el.name || '').toLowerCase();
		if (el.tagName.toLowerCase() === 'textarea') { return 'text'; }
		if (/email/.test(n)) { return 'email'; }
		if (/(^|_|\[)(code|uuid|alpha_2|alpha_3)(_|\]|$)/.test(n)) { return 'token'; }
		if (/(^|_|\[)(name|city|state|company)(\]|$)/.test(n) || n === 'name') { return 'name'; }
		if (/phone|zip/.test(n)) { return 'phone'; }
		return 'line';
	}
	function clean(el){
		if (!applies(el)) { return; }
		var v = el.value, k = kind(el), o = v;
		v = v.replace(WS, '');
		if (k === 'name' || k === 'phone') { v = v.replace(INNER, ' '); }
		else if (k === 'token' || k === 'email') { if (k === 'token') { v = v.replace(INNER, ''); } }
		if (v !== o) { el.value = v; }
	}
	document.addEventListener('blur', function(e){ clean(e.target); }, true);
	document.addEventListener('change', function(e){ clean(e.target); }, true);
	document.addEventListener('paste', function(e){ var t = e.target; setTimeout(function(){ if (applies(t) && kind(t) !== 'text') { clean(t); } }, 0); }, true);
	document.addEventListener('keydown', function(e){
		var t = e.target;
		if (!applies(t) || e.ctrlKey || e.metaKey) { return; }
		if ((e.key === ' ' || e.keyCode === 32) && (!t.value.length || (t.selectionStart === 0 && t.selectionEnd === 0))) { e.preventDefault(); return; }
		var k = kind(t);
		if ((e.key === ' ' || e.keyCode === 32) && (k === 'email' || k === 'token')) { e.preventDefault(); return; }
		if ((e.key === ' ' || e.keyCode === 32) && (k === 'name' || k === 'phone') && t.selectionStart > 0 && t.value.charAt(t.selectionStart - 1) === ' ') { e.preventDefault(); }
	}, true);
	document.addEventListener('submit', function(e){
		var f = e.target; if (!f || !f.elements) { return; }
		for (var i = 0; i < f.elements.length; i++) { clean(f.elements[i]); }
	}, true);
	// jQuery-validate / click driven submits: normalise just before any button inside a form is processed
	document.addEventListener('click', function(e){
		var b = e.target && e.target.closest ? e.target.closest('button, input[type=submit]') : null;
		var f = b && b.form;
		if (f) { for (var i = 0; i < f.elements.length; i++) { clean(f.elements[i]); } }
	}, true);
})();
