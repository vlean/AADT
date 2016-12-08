$(function(){

    var urlParams  =location.href.parseURL();
    var urlPath = urlParams.path;
    urlParams = urlParams.params;

    $('#params_add').click(function(){
        var empty_params = {data:[1]};
        addTemplate('params',empty_params);
        return false;
    });
    $('#resp_add').click(function(){
        var time = new Date().getTime();
        var empty_resp = {data:[{pid:0,id:time,level:0,key:'',must:0,name:'',type:'int',rule:'',remark:''}]};
        addTemplate('response',empty_resp);
        return false;
    });
    $('#err_add').click(function(){
        var empty_err = {data:[{errno:'',errmsg:''}]};
        addTemplate('errinfo',empty_err);
        return false;
    });
    $('#configs_add').click(function(){
        var empty_config = {data:[{name:'',key:''}]};
        addTemplate('config',empty_config);
        return false;
    });

    $('#version_add').click(function(){
        var version= $('.version_detail').val();
        if(version.trim().length== 0) return ;
        version = version.trim();
        var empty_ver = {data:[version]};
        addTemplate('version',empty_ver);
        return false;
    });

    $('#env_add').click(function(){
        var empty_env = {data:[{name:'',url:''}]};
        addTemplate('environment',empty_env);
        return false;
    });

    $('body').delegate('.remove_line','click',function(){
        $(this).parents('tr').remove();
        return false;
    });
    $('body').delegate('.version_remove','click',function(){
        $(this).remove();
        return false;
    });
    $('body').delegate('.add_line','click',function(){
        var $pr = $(this).parents('tr');

        var type = $pr.find('.types').val();
        gen_level = parseInt($pr.find('.resp_level').val());
        gen_pid = $pr.find('.resp_id').val();
        gen_id = new Date().getTime();
        switch (type) {
            case 'int':
            case 'str':
            case 'flo':
                break;
            case 'arr':
                gen_level= gen_level+2;
                break;
            case 'mod':
                gen_level= gen_level+1;
                break;
        }
        var data = {data:[{pid:gen_pid,id:gen_id,level:gen_level,type:'int',key:'',must:0,name:'',rule:'',remark:''}]};
        resp_html_tr = template('tmp_response', data);
        $pr.after(resp_html_tr);
        return false;
    });


    var id = $('input[name="id"]').val();
    var fid = $('input[name="fid"]').val();
    var apiid = 0;
    var acttype = type = urlParams.type;

    // if(id>0)  parseApi(id);
    // if(fid) parseGroup(fid);




    if(acttype == 'editgroup'){
        parseGroup(fid);
    }

    if(acttype == 'addgroup'){
        $('#group_env').hide();
        $('#group_version').hide();
        $('#group_params').hide();
        $('#group_config').hide();
    }
    if(acttype == 'run'){
        $('div#apiSelect select').change(function(){
            var apiid = $('div#apiSelect select:enabled').last().val();
            if(parseInt(apiid)>0){
                parseApiToRun(apiid);
            }
        });
        parseGroup(fid);
    }

    if(acttype=='addapi'){
        var tmp_id = urlParams.fid.split(',');
        tmp_id = tmp_id[0];
        changeGroup();
        parseApi(tmp_id,acttype);
    }
    if(acttype == 'editapi' || acttype=='export' || acttype=='cloneapi'){
         changeGroup();
         parseApi(id,acttype);
    }

    $('#runApi').click(function(){
        var postData = {};
        postData.env = $('#environment_run').val();
        postData.params = {};
        $.each($('#justRunParams input'),function(index){
            var key = $('#justRunParams input').eq(index).attr('name');
            var kval = $('#justRunParams input').eq(index).val();
            postData.params[key] = kval;
        });
        if($('#apiVersion select').val()){
            postData.params[$('#apiVersion select').attr('name')] = $('#apiVersion select').val();
        }
        postData.id = apiid;
        console.log(postData);
        $.ajax({
            type:"POST",
            dataType:"JSON",
            data:postData,
            url:"/Api/run",
            success:function(data){
                console.log(data);
                $("#response").JSONView(data.data.response);
                $("#resp_info").JSONView(data.data.info);
            }
        });
        return false;
    });

    function parseApiToRun(id){
        apiid = id;
        $.ajax({
            type:"POST",
            dataType:"JSON",
            data:{id:id},
            url:"/Api/getApiById",
            success:function(data){
                addTemplate('run_params',{data:data.data.detail.params});
                console.log(data.data.detail.params);
            }
        })
    }

    function parseGroup(fid){

        var group = urlParams.group;
        var type = urlParams.type;
        if(group){
            group = group.split(',');
            group.shift();
            group.reverse();
            if(urlParams.id) group.push(urlParams.id);
            $.each(group,function(index){
                $('#apiSelect select').eq(index).attr('data-value',group[index]);
            });
        }
        fids = fid.split(',');
        fid = fids[fids.length-1];

        $.ajax({
            type:"POST",
            dataType:"JSON",
            data:{fid:fid},
            url:"/Api/showAll",
            success:function(data){
                if(data.errno == 0){
                    optionList(data.data.list);
                    console.log(data);
                    if(type == 'run'){
                        if(data.data.group.version.trim() == ''){
                            $('#showApiVersion').hide();
                        }else{
                            var apiVersion = {data:data.data.group.detail.version};
                            $('#showApiVersion select').attr('name',data.data.group.version);
                        }
                        
                        addTemplate('run_version',apiVersion);
                        $('#showApiVersion select option:last').attr('selected','selected');
                        addTemplate('run_envs',{data:data.data.group.detail.environment});
                        $('#environment_run option').eq(1).attr('selected','selected');
                    }

                    if(type == 'editapi'){
                        if(data.data.group.detail.version.length>0){
                            addTemplate('versions',{data:data.data.group.detail.version});
                        }else{
                            $('#version_info').hide();
                        }
                    }

                    if(type == 'addapi'){

                    }

                    if(type == 'editgroup'){
                        group = data.data.group;
                        $('#title').val(group.title);
                        $('#desc').val(group.desc);
                        $('#version_key').val(group.version);
                        $('input[name="id"]').val(group.id);
                        $('input[name="fid"]').val(group.fid);
                        addTemplate('environment',{data:group.detail.environment});
                        addTemplate('config',{data:group.detail.config});
                        addTemplate('params',{data:group.detail.params});
                        addTemplate('version',{data:group.detail.version});

                        if(fids.length>1){
                            $('#group_env').hide();
                            $('#group_version').hide();
                            $('#group_params').hide();
                            $('#group_config').hide();
                        }
                    }


                }else{
                    console.log(data);
                }
            }
        })
    }

    function changeGroup() {
        var group = urlParams.fid.split(',');
        group.shift();
        $.each(group,function(index){
            $('#apiSelect select').eq(index).attr('data-value',group[index]);
        });

        $('div#apiSelect select').change(function(){
            var fids = [];
            var tmp_fid = urlParams.fid.split(',');
            fids.push(tmp_fid[0]);
            $.each($('div#apiSelect select:enabled'),function(index){
                var fid = $('div#apiSelect select:enabled').eq(index).val();
                if(fid>0){
                    fids.push(fid)
                }
            })
            fids = fids.join(',')
            console.log(fids);
            $('input[name="fid"]').val(fids);
        });
    }

    function optionList(data){
        if(!data.length || !$('#apiSelect').length) return;
        $('#apiSelect').cxSelect({
          selects: ['group', 'api1', 'api2', 'api3', 'api4'],
          jsonName: 'title',
          jsonValue: 'id',
          jsonSub: 'sub',
          data: data
        });
    }

    function parseApi(id,acttype){
        $.ajax({
            type:"POST",
            dataType:"JSON",
            data:{id:id,type:'group',acttype:acttype},
            url:"/Api/getApiById",
            success:function(data){
                if(data.errno == 0){
                    if(acttype == 'export'){
                        exportApi(data);
                    }else if(acttype == 'editapi' || acttype=='cloneapi'){
                        optionList(data.data.list);
                        editApi(data);
                    }else if(acttype == 'addapi'){
                        console.log(acttype);
                        optionList(data.data.list);
                        addTemplate('version',{data:data.data.detail.version})
                    }
                    console.log(data);
                }else{
                    console.log(data);
                }
            }
        })
    }

    function exportApi(data){
        data = data.data;
        detail = data.detail;

        $('#title').text(data.title);
        $('#url').text(data.url);
        $('#version').text(data.version);
        $('#desc').text(data.desc);
        addTemplate('params',{data:detail.params});
        addTemplate('response',{data:detail.responses});
        addTemplate('errinfo',{data:detail.errinfo});
        addTemplate('export_env',{data:data.group.detail.environment});
    }

    function editApi(data){
        data = data.data;
        detail = data.detail;
        $('input[name="title"]').val(data.title);
        $('input[name="url"]').val(data.url);
        $('input[name="version"]').val(data.version);
        $('#desc').val(data.desc);
        if(data.group.detail.version.length>0) addTemplate('versions',{data:data.group.detail.version});
        addTemplate('params',{data:detail.params});
        addTemplate('response',{data:detail.responses});
        addTemplate('errinfo',{data:detail.errinfo});
    }


    function addTemplate(temp,data){
        switch(temp){
            case 'version':
                var ver_html_tr = template('tmp_version',data);
                $('#version').append(ver_html_tr);
                break;
            case 'params':
                var params_html_tr = template('tmp_params',data);
                $('#params').append(params_html_tr);
                break;
            case 'response':
                var resp_html_tr = template('tmp_response', data);;
                $('#response').append(resp_html_tr);
                break;
            case 'config':
                var config_html_tr = template('tmp_config', data);;
                $('#configs').append(config_html_tr);
                break;
            case 'errinfo':
                var err_html_tr = template('tmp_errinfo', data);
                $('#errlist').append(err_html_tr);
                break;
            case 'environment':
                var ver_html_tr = template('tmp_environment',data);
                $('#environment').append(ver_html_tr);
                break;
            case 'run_params':
                $('#justRunParams').html('');
                var run_params_html_tr = template('run_params',data);
                $('#justRunParams').append(run_params_html_tr);
                break;
            case 'run_envs':
                $('#environment_run').html('');
                var run_envs_html_tr = template('run_envs',data);
                $('#environment_run').append(run_envs_html_tr);
                break;
            case 'export_env':
                var run_envs_html_tr = template('tmp_export_env',data);
                $('#environment').append(run_envs_html_tr);
                break;
            case 'run_version':
                var version_html_tr = template('run_version',data);
                $('#apiVersion select').append(version_html_tr);
                break;
            case 'versions':
                var run_version_html_tr = template('tmp_version',data);
                $('#version_info #version').append(run_version_html_tr);
                break;
        }
    }


    function parseRules(rulestr) {
        $.ajax({
            url:'/Api/parseRule',
            data:{rules:rulestr},
            dataType:'JSON',
            type:'POST',
            success:function(data){
                console.log(data);
                return data.data;
            },
            error:function(data){
                return null;
            }

        });
    }
});




String.prototype.parseURL = function(){
    var url =this.toString()
    var a = document.createElement('a');
    a.href = url;
    return {
        source: url,
        protocol: a.protocol.replace(':', ''),
        host: a.hostname,
        port: a.port,
        query: a.search,
        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
        hash: a.hash.replace('#', ''),
        path: a.pathname.replace(/^([^\/])/, '/$1'),
        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
        segments: a.pathname.replace(/^\//, '').split('/'),
        params: (function() {
            var ret = {};
            var seg = a.search.replace(/^\?/, '').split('&').filter(function(v,i){
                if (v!==''&&v.indexOf('=')) {
                    return true;
                }
            });
            seg.forEach( function(element, index) {
                var idx = element.indexOf('=');
                var key = element.substring(0, idx);
                var val = element.substring(idx+1);
                ret[key] = val;
            });
            return ret;
        })()
    };
}