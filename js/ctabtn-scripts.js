/* CTA Button Styler */

(function($) {
    // $.noConflict();
    let emtz = {};
    //console.log('ACTIVATED');
    // Add Color Picker to all inputs that have 'color-field' class
    if ($('.cta-button-color-pick').length > 0) {
        $('.cta-button-color-pick').wpColorPicker();
    }

    if ($('.cta101').length > 0) {
        //console.log('FOUND');
        //clear any potentially destructive styles
        if ($('.cta101>a').length > 0) {
            const emt = $('.cta101>a');
            // emt.css('padding','0');
            // emt.css('background','transparent');
            // emt.css('color','#000');
            emt.removeAttr("color")
        }
        $('.cta101').each(function () {
            const emt = $(this);
            const emt1 = $(this).children('a:first-child')
            emt.removeAttr('style');
            emt1.css('padding', '0');
            emt1.css('background', 'transparent');
            //console.log('ELEMENT', emt1);
            let aj = $.ajax({
                url: ctabtn.ajax_url,
                type: 'post',
                data: {action: 'ctabtn_fetch_styles', my_class: 'cta101'},
                dataType: 'json',
                success : function(data,status){
                    emtz=data;
                    if (typeof data === 'object' && data !== null) {
                        Object.keys(data).forEach(key => {
                            //console.log("\n" + key + ": " + data[key][0] + " | " + data[key][1]);
                            emt.css(key, data[key][0]);
                            emt.hover(
                                function() {
                                    $( this ).css(key,data[key][1]);
                                }, function() {
                                    $( this ).css(key,data[key][0]); //to remove property set it to ''
                                }
                            );
                            if (typeof emt1 !== null){
                                if (key == 'color'){
                                    emt1.css(key, data[key][0]);
                                    emt1.hover(
                                        function() {
                                            $( this ).css(key,data[key][1]);
                                        }, function() {
                                            $( this ).css(key,data[key][0]); //to remove property set it to ''
                                        }
                                    );
                                }
                            }
                        });
                    } else {
                        console.log('ERROR', 'No CTA style data to show');
                    }
                },
                error: function (xhr, status, errorThrown) {
                    let mytxt = '<h3>ERROR</h3><p>' + status + ' [' + errorThrown + ']<br />' + xhr.responseText + '</p>';
                    console.log(mytxt);
                }
            });
            aj.then(function(){
                $(".cta_message").hide();
                if ( emtz.zz_shake == 1 ) dampshake('cta101',100,15,23);
                if ( emtz.zz_buzz == 1 ) buzzkit('cta101',700,15);
                if ( emtz.zz_blink == 1 ) blinkin('cta101',5000,3)
            })
        });
        //remove drop-arrow in Divi
        if ($('.cta101').hasClass('menu-item-has-children')){
            $('.cta101').removeClass('menu-item-has-children');
        }
    }

    function callback() {
        // setTimeout(function() {
        //     $( ".cta101" ).removeAttr( "style" ).hide().fadeIn();
        // }, 1000 );
    };

    function dampshake(cls='cta101', interval=300, distance=10,times=20) {
        $("."+cls).css('position', 'relative');
        for (var iter = 0; iter < times ; iter++) {
            let x = parseInt(distance *-(times-iter)/times);
            $("."+cls).animate({
                left: ((iter % 2 == 0 ? distance : x))
            }, (interval*(1.5-(times-iter)/times)));
        }
        $("."+cls).animate({ left: 0 }, interval);
    }

    function blinkin(cls='cta101', interval=1000, times=20) {
        for (var iter = 0; iter < times ; iter++) {
            $("."+cls).fadeOut(interval);
            $("."+cls).fadeIn(interval);
        }
    }

    function buzzkit(cls='cta101', interval=1000, times=20) {
        $( ".cta101" ).effect( 'pulsate', {times:times}, interval, callback );
        //$( ".cta101" ).effect( "highlight", {color:"#669966"}, 3000 );
        //$( ".cta101" ).toggle("pulsate",1000);
    }

})( jQuery );

