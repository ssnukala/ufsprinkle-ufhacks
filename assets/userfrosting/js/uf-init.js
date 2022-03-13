/**
 * Contains code that should be initialized in all UF pages.
 */

window.onload = function() { // can also use window.addEventListener('load', (event) => {
    // Display page alerts
    //console.log("Line 15 looking for alerts now");
    if ($("#alerts-page").length) {
        $("#alerts-page").ufAlerts();
        $("#alerts-page").ufAlerts('fetch').ufAlerts('render');
    }
    //console.log("Line 20 done looking for alerts now");

};
$(document).ready(function() {

    // Override Bootstrap's tendency to steal focus from child elements in modals (such as select2).
    // See https://github.com/select2/select2/issues/1436#issuecomment-21028474
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    // Link all copy buttons
    $.uf.copy('.js-copy-trigger');

    // Set any JS variables that might be missing from config.js.twig
    if (typeof site.uf_table === 'undefined') {
        site['uf_table'] = {
            use_loading_transition: true
        };
    }
});