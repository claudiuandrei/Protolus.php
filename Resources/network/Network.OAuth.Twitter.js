Network.OAuth.Twitter = new Class({
   Implements: Options,
   options:{
      app_id : '',
      require : true,
      site_domain : "stylepage.com",
      onLoad : function(){}
   },
   initialize : function(options){
      this.setOptions(options);
      if(this.options.onLoad){
         this.options.onLoad();
      }
   }, 
   require : function(callback){
      if(callback) callback();
   },
   authCredentials : function(callback){
      var site_domain = this.options.site_domain;
              var oauth = new oauthPopUp('twitter', { path : '/signup/twitter', 
                                                      'autoOpen':true, 
                                                      'directComm' : true,
                                                      'domain' : site_domain,
                                                      'specs' : {   
                                                          height: 700
                                                      },
                                                      callback: function(message){ 
                                                          var payload = {};
                                                          var keyVals = message.split(",");
                                                          if(keyVals.length > 0){
                                                              var i=0;
                                                              while(i < keyVals.length){
                                                                  var parts = keyVals[i].split(":");
                                                                  if(parts.length >= 2){
                                                                      payload[parts[0]] = parts[1];
                                                                  }
                                                                  i++;
                                                              }
                                                              payload['type'] = 'twitter';
                                                              payload['referral'] = Cookie.read('referral');
                                                              if(window.user) payload['user_id'] = window.user.user_id;
                                                              callback(payload);
                                                          }
                                                      }
                                                  }
                          );
                          
   }
});