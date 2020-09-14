var plugin_backup = {
    timer: null,
    $log: null,

    start: function() {
        plugin_backup.$log = jQuery('.plugin_backup .log');
        plugin_backup.timer = window.setInterval(function() {
            plugin_backup.$log.scrollTop(plugin_backup.$log[0].scrollHeight);
        }, 100);
    },

    stop: function() {
        if (plugin_backup.timer) {
            window.clearInterval(plugin_backup.timer);
            plugin_backup.timer = null;
        }
        jQuery('.plugin_backup .running').hide();
        plugin_backup.$log.scrollTop(plugin_backup.$log[0].scrollHeight);
    }
};

var btColl = document.getElementsByClassName("collapsible");
for (var btIter = 0; btIter < btColl.length; i++) {
    btColl[btIter].addEventListener("click", function() {
        document.getElementsByClassName('bt-content')[0].style.display = 'block';
        document.getElementsByClassName('bt-warning')[0].style.display = 'none';
    });
}