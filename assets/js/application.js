$( document ).ready(function() {

    var hash = window.location.hash;
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');
    
    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
    });


var Unit = function(text) {
    this.text = text;
    this.class = "";
    this.toggleClass = function(className) {
        if (this.class === className) {
            this.class = "";
        } else {
            this.class = className;
        }
        console.log("Set class to: " + this.class);
    }
};

var onkeydown = (function (ev) {
    var key;
    var isShift;
    if (window.event) {
        key = window.event.keyCode;
        isShift = !!window.event.shiftKey; // typecast to boolean
    } else {
        key = ev.which;
        isShift = !!ev.shiftKey;
    }
    if ( isShift ) {
        switch (key) {
            case 16: // ignore shift key
                break;
            default:
                alert(key);
                // do stuff here?
                break;
        }
    }
});

Vue.directive('on').keyCodes.shift = 16;

var demo = new Vue({
    el: '#aligner-editor',
    data: {
        language0: [],      // List of translation units in source language
        language1: [],      // List of translation units in destination language
        selected: []        // List of selected translation units
    },
    ready: function() {
        this.load_tmx();
    },
    methods: {
        load_tmx: function() {
            this.$http.get('/tmx/get_chunks').then((response) => {
                this.map_items("language0", response.body.language_0);
                this.map_items("language1", response.body.language_1);
            }, (response) => {
                console.error("Error: " + response.status);
            });
        },
        map_items: function(variable, list) {
            var self = this;
            list.forEach(function(item) {
                self.$get(variable).push(new Unit(item));
            });
        },
        save: function() {
            var data = {
                'language0': this.$get('language0'),
                'language1': this.$get('language1')
            };

            console.log(data);

            this.$http.post('/tmx/save_chunks', data).then((response) => {
                console.log('data has been saved');
                this.load_tmx();
            }, (response) => {
                console.error('Error saving TMX data with response code ' + response.code);
            });
        },
        remove: function(target, item) {
            this.$get(target).$remove(item);
        },
        duplicate: function(target, item, index) {
            console.log("Duplicating element at index: " + index);
            this.$get(target).splice(index, 0, item);
        },
        select: function(target, item, event) {
            //TODO: Check if SHIFT is pressed
            if (item === undefined) {
                console.error("Item is not defined");
                return;
            }

            if (event.shiftKey) {
                item.toggleClass("active");
                this.$get("selected").push(item);
                console.log(this.$get("selected"));
            }
        },
        merge: function() {
            console.log("Merging selected items");
            //TODO: Merge the selected cells
            this.$set('selected', []);
        },

        //Split the item at the place of newline
        split: function(item) {
            console.log("Enter pressed");
            console.log(item.text);
        },

        shift: function() {
            console.log("Shift pressed");
        },
        clear: function() {
            this.$get("selected").forEach(function(item) {
                item.toggleClass("active");
            });
            this.$set("selected", []);

        }
    }
});


});

