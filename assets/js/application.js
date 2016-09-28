$( document ).ready(function() {

    var hash = window.location.hash;
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');
    
    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
    });

/**
    var redips = {
        table: REDIPS.table
    };

    redips.init = function () {
        var rt = REDIPS.table;
        rt.onmousedown('tbl-align', true);
        rt.color.cell = "#ffccff";
        # rt.cell_index(true); //set 'true' for debug mode
        # rt.color.cell = '#ffccff';
    }

    redips.merge = function() {
        //redips.table.merge('h', false);
        redips.table.merge('v', false);
    }

    redips.split = function(mode) {
        redips.table.split(mode);
    }

    redips.init();


    $('#btn-merge').on('click', function() {
        redips.merge();
    });

    $('#btn-split').on('click', function() {
        redips.split();
    });

    $('#btn-delete').on('click', function() {
        REDIPS.row;
        console.log(REDIPS.table);
    });
**/

/***
new Vue({
    el: '#app',
    data: {
        columns: [
            'name',
            'nickname',
            'email',
            'birthdate',
            'gender',
            '__actions'
        ],
        itemActions: [
            { name: 'view-item', label: '', icon: 'zoom icon', class: 'ui teal button' },
            { name: 'edit-item', label: '', icon: 'edit icon', class: 'ui orange button'},
            { name: 'delete-item', label: '', icon: 'delete icon', class: 'ui red button' }
        ]
    },
    methods: {
        viewProfile: function(id) {
            console.log('view profile with id:', id)
        }
    },
    events: {
        'vuetable:action': function(action, data) {
            console.log('vuetable:action', action, data)
            if (action == 'view-item') {
                this.viewProfile(data.id)
            }
        },
        'vuetable:load-error': function(response) {
            console.log('Load Error: ', response)
        }
    }
})
***/


var Unit = function(text) {
    this.text = text;
}


var demo = new Vue({
    el: '#aligner-editor',
    data: {
        language0: [],
        language1: []
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
            this.$get(target).splice(index, index, item);
        },
        select: function(target, item) {
            item.toggleClass("active");
        },
        split: function() {
            console.log("Enter presse");
        }
    }
})


});

