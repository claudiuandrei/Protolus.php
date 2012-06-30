//global js here

(function(){
	window.addEvent('domready', function() {
		var myAccordion = new Fx.Accordion($$('.toggle span'), $$('.year-wrapper'), { display: 2, alwaysHide: false });
   });
})();