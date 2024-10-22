if(typeof(String.prototype.trim) === "undefined")
{
    String.prototype.trim = function() 
    {
        return String(this).replace(/^\s+|\s+$/g, '');
    };
}

function getCaretCharacterOffsetWithin(element) {
    var caretOffset = 0;
    var doc = element.ownerDocument || element.document;
    var win = doc.defaultView || doc.parentWindow;
    var sel;
    if (typeof win.getSelection != "undefined") {
        sel = win.getSelection();
        if (sel.rangeCount > 0) {
            var range = win.getSelection().getRangeAt(0);
            var preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            //console.log(preCaretRange.toString().trim());
            caretOffset = preCaretRange.toString().trim().length;
        }
    } else if ( (sel = doc.selection) && sel.type != "Control") {
        var textRange = sel.createRange();
        var preCaretTextRange = doc.body.createTextRange();
        preCaretTextRange.moveToElementText(element);
        preCaretTextRange.setEndPoint("EndToEnd", textRange);
        caretOffset = preCaretTextRange.text.length;
    }
    return caretOffset;
}

function cleanTable(vue) {
  for (t = 0; t <= 1; t++) {
    var target = 'language' + t;
    for (var el = vue.$get(target).length - 1; el > 0; el--)
      if (vue.$get(target)[el].text == '  ') {
        console.log("Removing spacer in " + target);
        vue.$get(target).$remove(vue.$get(target)[el]);
     }
  }
}

function addSpacer(vue, target) {
    var end = vue.$get(target).length - 1;
    var item = vue.$get(target)[end];
    vue.$get(target).splice(end, 0, jQuery.extend(true, {}, item));
    item.text = "  ";
    item.target = target;
    console.log("Adding spacer");
}

function alignTable(vue) {
  var el = vue.$get("language0").length;
  var el1 = vue.$get("language1").length;
  for (i = el; i < el1; i++) 
    addSpacer(vue, 'language0');
  for (i = el1; i < el; i++) 
    addSpacer(vue, 'language1');
  el = vue.$get("language0").length;
  if (el % 2 != 0) addSpacer(vue, 'language0');

  for (t = 0; t <= 1; t++) {
    var target = 'language' + t;
    for (var el = 0; el < vue.$get(target).length; el++)
      vue.$get(target)[el].index = el;
  }
}

$( document ).ready(function() {
    $.ajax({url: 'https://skuuper.com/users/me.json', xhrFields: {withCredentials: true}})
      .success(function(status, e) {
        //console.log("Login fine!");
      })
      .fail(function(status) {
        console.log("Error logging in: " + status.statusText);
        //location.href = "http://skuuper.com/en/users/sign_in";
        //+ TODO: show unauthorized; open auth in new window
      });

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
};	// function Unit


Vue.directive('on').keyCodes.shift = 16;

var demo = new Vue({
    el: '#aligner-editor',
    data: {
        language0: [],      // List of translation units in source language
        language1: [],      // List of translation units in destination language
        selected: [],       // List of selected translation units
        deleted: [],        // List of deleted cells
        split_pos: -1,      // Position of a cursor within a chunk for splitting before catching mouse click on a button
        split_item: -1,     // Item with cursor
        cur_item: -1,       // Current item edited
        cur_text: ""        // New current item text
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
                alignTable(this);
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
            cleanTable(this);
            item.position = index;
            item.target = target;
            this.$get("deleted").push(item);
            this.$get(target).$remove(item);
            alignTable(this);
        },
        removeLine: function(target, item, index) {
            //~ Determining the real position
            cleanTable(this);
            var ind = item.index;
            console.log("Looking for index " + ind);
            for (i = 0; i < this.$get("language0").length; i++) 
              if (this.$get("language0")[i].index == item.index) 
                ind = i;
            item0 = this.$get("language0")[ind];
            item0.target = " language0";
            for (i = 0; i < this.$get("language1").length; i++) 
              if (this.$get("language1")[i].index == item.index) 
                ind = i;
            item1 = this.$get("language1")[ind];
            item1.target = " language1";
            this.$get("deleted").push(item0);
            this.$get("deleted").push(item1);
            this.$get("language0").$remove(item0);
            this.$get("language1").$remove(item1);
            alignTable(this);
        },
        duplicate: function(target, item, index) {
            cleanTable(this);
            var item = (JSON.parse(JSON.stringify(item)));
            this.$get(target).splice(index, 0, item);
            alignTable(this);
        },
        empty: function(target, item, index) {
            this.$get(target).splice(index, 0, jQuery.extend(true, {}, item));
            item.text = " ";
        },
        select: function(target, item, event) {
            if (item === undefined) {
                console.error("Item is not defined");
                return;
            }
            if (event.shiftKey) {
                if (this.$get("selected").length > 0) {
                  if (item.target != this.$get("selected")[0].target) {
                    return;
                  }
                }
                if (item.class == "active") {
                  var index = this.$get("selected").indexOf(item);
                  if (index > -1) {
                    this.$get("selected").splice(index, 1);
                  }
                } else
                    this.$get("selected").push(item);
                item.toggleClass("active");
            }
        },
        merge: function(target, item, index) {
            cleanTable(this);
            var self = this;

            var first = this.$get("selected")[0];
            first.toggleClass("selected");
            self.$get("selected").$remove(first);

            this.$get("selected").forEach(function(item) {
                spacer = first.text.trim().endsWith(".") ? " " : "";
                first.text += spacer + item.text;
                item.toggleClass('active');
                //console.log(target);
                self.$get(target).$remove(item);
            });
            this.$set('selected', []);
            alignTable(this);
        },
        splitHover: function(target, item, index, lang) {
            if (!item.text || item.text.length < 1)
              return;
            var el = target.target.parentElement.parentElement.children[0];
            var pos = getCaretCharacterOffsetWithin(el);	// Magic number defined by layout length
            this.$set("split_pos", pos);
            this.$set("split_item", index);
        },
        uHout: function(it) {
            item = this.$get("cur_item");
            if (item != it)
              return;
            txt = this.$get("cur_text");
            if (item.text != txt && txt.length > 0)
              item.text = txt;
            this.$set("cur_text", "");
        },
        uHover: function(item) {
            this.$set("cur_item", item);
        },
        update: function (e) {
            this.$set("cur_text", e.target.innerText);
        },
        split: function(target, item, index) {
            cleanTable(this);
            if (!item.text || item.text.length < 1)
              return;
            var pos = this.$get("split_pos");
            if (this.$get("split_item") != index || pos < 1 || pos > item.text.length)
               return;
            var text = item.text.trim();
            //console.log(text);
            //console.log(this.$get(item.target)[index].text);
            item.text = text.substring(0, pos);
            var created = new Unit(text.substring(pos));
            created.index = item.index + 1;
            created.target = item.target;
            this.$get(item.target).splice(index + 1, 0, created);
            alignTable(this);
        },
        clear: function() {
            this.$get("selected").forEach(function(item) {
                item.toggleClass("active");
            });
            this.$set("selected", []);
        },
        undo: function() {
            cleanTable(this);
            var item = this.$get("deleted").pop();
            if (item.target.startsWith(" "))
            {
              var item2 = this.$get("deleted").pop();
              this.$get(item2.target.trim()).splice(item2.index, 0, item2);
            }
            this.$get(item.target.trim()).splice(item.index, 0, item);
            alignTable(this);
        }
    }
});


});

