(function($){
    $(function(){

        $('.button-collapse').sideNav();

        var timeagoInstance = timeago();
        var node_SubmitTime = document.querySelectorAll('.workerLastSubmitTime');
        var node_uptime = document.querySelectorAll('.rendered_uptime');
        var next_payout = document.querySelectorAll('.next_payout2');

        // use render method to render nodes in real time
        timeagoInstance.render(node_SubmitTime, 'en');
        timeagoInstance.render(node_uptime, 'en');
        timeagoInstance.render(next_payout, 'en');
        

        // cancel real-time render for every node
        //timeago.cancel()
        // or for the specific one
        //timeago.cancel(nodes[0])

        // Refresh page every 60 secs
        //setTimeout(function(){
        //window.location.reload(1);
        //}, 60000);

    }); // end of document ready
})(jQuery); // end of jQuery name space

