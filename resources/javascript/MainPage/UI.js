/**
 * UI object to hold UI functionality not related to the form
 *
 * @type {{}}
 */
const UI = {

    hideSidebar: function() {
        document.querySelector(".sidebar").hidden = true;
        document.querySelector(".sidebar__toggler").hidden = false;
    },

    showSidebar: function() {
        document.querySelector(".sidebar__toggler").hidden = true;
        document.querySelector(".sidebar").hidden = false;
    },

    showHelpSidebar: function(help = 'Help') {
        document.querySelector(".help-toggler").hidden = true;
        document.querySelector(".help-sidebar").hidden = false;
        if (help !== '') {
            document.getElementById('help-content').innerHTML = getHelpText('Help');
        }
    }
};
