var plugin_backup = {
    timer: null,
    $log: null,

    start: function () {
        plugin_backup.$log = jQuery('.plugin_backup .log');
        plugin_backup.timer = window.setInterval(function () {
            plugin_backup.$log.scrollTop(plugin_backup.$log[0].scrollHeight);
        }, 100);
    },

    stop: function () {
        if (plugin_backup.timer) {
            window.clearInterval(plugin_backup.timer);
            plugin_backup.timer = null;
        }
        jQuery('.plugin_backup .running').hide();
        plugin_backup.$log.scrollTop(plugin_backup.$log[0].scrollHeight);
    }
};
