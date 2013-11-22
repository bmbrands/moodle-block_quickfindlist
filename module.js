M.block_search_users = {

    sesskey: null,

    init: function(Y, courseformat, courseid, sesskey) {
        this.Y = Y;
        if (this.instances === undefined) {
            this.instances = new Array();
        }
        this.sesskey = sesskey;

        var instance = {
            'courseformat': courseformat,
            'courseid': courseid,
            'progress': Y.one('#quickfindprogress'),
            'xhr': null
        }
        this.instances = instance;
        Y.on('keyup', this.search_on_type, '#search_userssearch');
        Y.on('submit', this.search_on_submit, '#quickfindform');

        var searchfocus = function(e) {
          console.log('focus');
          Y.one('#search_userssearch').focus();
        }

        Y.one('body').delegate('click', searchfocus, '.block_search_users .block-toggle');
    },

    search_on_type: function(e) {
        var searchstring = e.target.get('value');
        M.block_search_users.search(searchstring);
    },

    search_on_submit: function(e) {
        e.preventDefault();
        var searchstring = e.target.getById('search_userssearch').value;
        M.block_search_users.search(searchstring);
    },

    search: function(searchstring) {

        var Y = this.Y;
        var instance = this.instances;

        uri = M.cfg.wwwroot+'/blocks/search_users/quickfind.php';
        if (instance.xhr != null) {
            instance.xhr.abort();
        }
        instance.progress.setStyle('visibility', 'visible');

        instance.xhr = Y.io(uri, {
            data: 'name='+searchstring
                +'&courseformat='+instance.courseformat
                +'&courseid='+instance.courseid
                +'&sesskey='+this.sesskey,
            context: this,
            on: {
                success: function(id, o) {
                    var response = Y.JSON.parse(o.responseText);

                    var instance = this.instances;
                    var list = Y.Node.create('<ul />');
                    for (p in response.people) {

                        var userpicture = '';
                        if (response.people[p].picture > 1) {
                          userpicture = M.cfg.wwwroot+'/pluginfile.php/'+response.people[p].contextid+'/user/icon/standard/f2';
                        } else {
                          userpicture =  M.util.image_url('u/f2', 'moodle');
                        }
                        li = Y.Node.create('<li><a href="'+M.cfg.wwwroot+'/user/profile.php?id='+response.people[p].id+'"><img src='+userpicture+'>'+response.people[p].firstname+' '+response.people[p].lastname+'</a></li>');
                        list.appendChild(li);
                    }
                    instance.progress.setStyle('visibility', 'hidden');
                    Y.one('#search_users').replace(list);
                    list.set('id', 'search_users');
                },
                failure: function(id, o) {
                    if (o.statusText != 'abort') {
                        var instance = this.currentinstance;
                        instance.progress.setStyle('visibility', 'hidden');
                        if (o.statusText !== undefined) {
                            instance.listcontainer.set('innerHTML', o.statusText);
                        }
                    }
                }
            }
        });
    }
}
