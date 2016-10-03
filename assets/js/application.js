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
    this.target = "";
    this.position = 0;
    this.toggleClass = function(className) {
        if (this.class === className) {
            this.class = "";
        } else {
            this.class = className;
        }
    }
};


Vue.directive('on').keyCodes.shift = 16;

var demo = new Vue({
    el: '#aligner-editor',
    data: {
        language0: [],      // List of translation units in source language
        language1: [],      // List of translation units in destination language
        selected: [],       // List of selected translation units
        deleted: []         // List of deleted cells
    },
    ready: function() {
        this.load_tmx();
    },
    methods: {
        clean_tmx: function() {
            this.$set('language0', []);
            this.$set('language1', []);
        },
        load_tmx: function() {
            this.$http.get('/aligner/get_chunks').then((response) => {
                this.map_items("language0", response.body.language_0);
                this.map_items("language1", response.body.language_1);
            }, (response) => {
                console.error("Error: " + response.status);
            });
        },
        map_items: function(target, list) {
            var self = this;
            var k = 0;
            var unit;
            list.forEach(function(item) {
                unit = new Unit(item);
                unit.index = k;
                unit.target = target;
                self.$get(target).push(unit);
                k++;
            });
        },
        save: function() {
            var data = {
                'language0': this.$get('language0'),
                'language1': this.$get('language1')
            };

            this.$http.post('/aligner/save_chunks', data).then((response) => {
                this.clean_tmx();
                this.load_tmx();
            }, (response) => {
                console.error('Error saving TMX data with response code ' + response.code);
            });
        },
        remove: function(target, item, index) {
            item.position = index;
            item.target = target;
            this.$get("deleted").push(item);
            this.$get(target).$remove(item);
        },
        duplicate: function(target, item, index) {
            this.$get(target).splice(index, 0, item);
        },
        select: function(target, item, event) {
            if (item === undefined) {
                console.error("Item is not defined");
                return;
            }
            if (event.shiftKey) {
                item.toggleClass("active");
                this.$get("selected").push(item);
            }
        },
        merge: function(target) {

            var self = this;

            var first = this.$get("selected")[0];
            first.toggleClass("selected");
            self.$get("selected").$remove(first);

            this.$get("selected").forEach(function(item) {
                first.text += " " + item.text;
                item.toggleClass('active');
                console.log(target);
                self.$get(target).$remove(item);
            });
            this.$set('selected', []);
        },
        split: function(item, event) {
            var lines = event.target.innerText.trim().split("\n");
            item.text = lines[0];
            event.target.innerText = item.text;

            if (lines.length < 2) {
                return;
            }
            var created = new Unit(lines[1]);
            created.index = item.index - 1;
            created.target = item.target;
            this.$get(item.target).splice(item.index + 1, 0, created);

            //TODO: Hack, need to investigate why the aligner does not update
            this.$get(item.target)[(item.index + 1)].innerText = lines[1];
        },
        clear: function() {
            this.$get("selected").forEach(function(item) {
                item.toggleClass("active");
            });
            this.$set("selected", []);
        },
        undo: function() {
            var item = this.$get("deleted").pop();
            console.log(item);
            this.$get(item.target).splice(item.index, 0, item);
        }
    }
});


});

