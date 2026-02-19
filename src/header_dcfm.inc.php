<?php
$header = (!isset($header)) ? $config->ParameterArray["OrgName"] : $header;
$subheader = (!isset($subheader)) ? "" : $subheader;
$version = $config->ParameterArray["Version"];
?>

<!-- Ganti Header dan menambahkan condition-condition untuk rights -->
<!-- Diubah Oleh Firdauz Fanani 23 April 2018 -->

<style>
    .navbar-template {
        padding: 40px 15px;
    }

    @media (min-width: 767px) {
        .navbar-nav .dropdown-menu .caret {
            transform: rotate(-90deg);
        }
    }
</style>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/navbar.css" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
<link href="css/demo.css" rel="stylesheet" type="text/css" />
<link href="css/search.css" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow" rel="stylesheet"> 

<style type="text/css">
    #topNav ul { max-height:600px; overflow-y:auto; }
</style>

<!-- HEADER CODE START -->
<header class="navbar" id="header-navbar">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">
                    <img src="images/dcim_logo.png" class="image-responsive" style="margin:-13px 0px 0px 8px;width: 170px;height: 47px;">
                </a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class=""><a href="index.php" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-home"></i> Home <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li class=""><a href="index_dcim.php"> VIO DCIM </b></a></li>
                            <li class=""><a href="index_dcfm.php"> DCFM </b></a></li>
                            <li class=""><a href="index_ithardware.php"> IT Hardware Management </b></a></li>
                        </ul>
                    </li>
                    <li class=""><a href="facpowatt.php"> Power (Single Line Diagram) </b></a></li>
                    <li class=""><a href="javascript:void(0);"> Cooling System </b></a></li>
                    <li class=""><a href="javascript:void(0);"> Security </b></a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<script src="scripts/navbar.js"></script>

<!-- HEADER CODE END -->

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!-- Include all compiled plugins (below), or include individual files as needed -->
<!--         <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> -->

<!-- Latest compiled and minified CSS -->
<!--         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"></link> -->

<!-- Optional theme -->

<!-- Latest compiled and minified JavaScript -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script> -->

<script type="text/javascript">
    function addlookup(inputobj, lookuptype) {
// clear any existing autocompletes
        if (inputobj.hasClass('ui-autocomplete-input')) {
            inputobj.autocomplete('destroy');
        }
// clear out previous search arrows
        inputobj.next('.text-arrow').remove();
// Position the arrow
        var inputpos = inputobj.position();
        var arrow = $('<div />').addClass('text-arrow');
        arrow.click(function () {
            inputobj.autocomplete("search", "");
        });
// add the autocomplete
        inputobj.autocomplete({
            minLength: 0,
            delay: 600,
            autoFocus: true,
            source: function (req, add) {
                $.getJSON('scripts/ajax_search.php?' + lookuptype, {q: req.term}, function (data) {
                    var suggestions = [];
                    $.each(data, function (i, val) {
                        suggestions.push(val);
                    });
                    ey
                    add(suggestions);
                });
            },
            open: function () {
                $(this).autocomplete("widget").css({'width': inputobj.width() + 6 + 'px'});
            }
        }).next().after(arrow);
        arrow.css({'top': inputpos.top + 'px', 'left': inputpos.left + inputobj.width() - (arrow.width() / 2)});
    }
    $('#advsrch, #searchadv ~ .ui-icon.ui-icon-close').click(function () {
        var here = $(this).position();
        $('#searchadv, #searchname').val('');
        $('#searchadv').parents('form').height(here.top).toggle('slide', 200).removeClass('hide');
        if ($('#searchadv').hasClass('ui-autocomplete-input')) {
            $('#searchadv').autocomplete('destroy');
        }
        if ($(this).text() == '<?php echo __("Advanced"); ?>') {
            $(this).text('<?php echo __("Basic"); ?>');
            $('#searchadv ~ select[name="key"]').trigger('change');
        } else {
            $(this).text('<?php echo __("Advanced"); ?>');
        }
    });
</script>
<script type="text/javascript" src="scripts/mktree.js"></script> 
<script type="text/javascript" src="scripts/konami.js"></script> 

<?php
/*
  function buildmenu($menu) {
  $level = '';
  foreach ($menu as $key => $item) {
  $level .= "<li>";
  if (!is_array($item)) {
  $level .= "$item";
  } else {
  $level .= "<a>$key</a><ul>";
  $level .= buildmenu($item);
  $level .= "</ul>";
  }
  $level .= "</li>";
  }
  return $level;
  }

  $menu = buildmenu(array_merge_recursive($rmenu, $rrmenu, $camenu, $wamenu, $samenu, $lmenu));

  print "
  <div style='margin-left:10px;'>
  <a href=\"index.php\">" . __("Home") . "</a>\n";

  $lang = GetValidTranslations();
  //strip any encoding info and keep just the country lang pair
  $locale = explode(".", $locale);
  $locale = $locale[0];
  echo '  <div class="langselect hide">
  <label for="language">Language</label>
  <select name="language" id="language" current="' . $locale . '">';
  foreach ($lang as $cc => $translatedname) {
  // This is for later. For now just display list
  //$selected=""; //
  if ($locale == $cc) {
  $selected = " selected";
  } else {
  $selected = "";
  }
  print "\t\t\t<option value=\"$cc\"$selected>$translatedname</option>";
  }
  echo '      </select>
  </div>

  <div id="nav_placeholder"></div>'; */
// Moved the navigation menu to an ajax load item   
?>
</div>
<script type="text/javascript">

    $("#sidebar .nav a").each(function () {
        var loc = window.location;
        if ($(this).attr("href") == "<?php echo basename($_SERVER['SCRIPT_NAME']); ?>" || $(this).attr("href") == loc.href.substr(loc.href.indexOf(loc.host) + loc.host.length + 1)) {
            $(this).addClass("active");
            $(this).parentsUntil("#ui-id-1", "li").children('a:first-child').addClass("active");
        }
    });
    $("#sidebar .nav").menu();

    $('#searchname').width($('#sidebar').innerWidth() - $('#searchname ~ button').outerWidth());
    addlookup($('#searchname'), 'name');
    $('#searchadv ~ select[name="key"]').change(function () {
        addlookup($('#searchadv'), $(this).val())
    }).outerHeight($('#searchadv').outerHeight()).outerWidth(157);

// Really long cabinet / zone / dc combinations are making the screen jump around.
// If they make this thing so big it's unusable, fuck em.
    $('#sidebar > hr ~ div').css({'width': $('#sidebar > hr ~ ul').width() + 'px', 'overflow': 'hidden'});

    function resize() {
        // Reset widths to make shrinking screens work better
        $('#header,div.main,div.page').css('width', 'auto');
        // This function will run each 500ms for 2.5s to account for slow loading content
        var count = 0;
        subresize();
        var longload = setInterval(function () {
            subresize();
            if (count > 4) {
                clearInterval(longload);
                window.resized = true;
            }
            ++count;
        }, 500);

        function subresize() {
            // page width is calcuated different between ie, chrome, and ff
            $('#header').width(Math.floor($(window).outerWidth() - (16 * 3))); //16px = 1em per side padding
            var widesttab = 0;
            // make all the tabs on the config page the same width
            $('#configtabs > ul ~ div').each(function () {
                widesttab = ($(this).width() > widesttab) ? $(this).width() : widesttab;
            });
            $('#configtabs > ul ~ div').each(function () {
                $(this).width(widesttab);
            });

            if (typeof getCookie == 'function' && getCookie("layout") == "Landscape") {
                // edge case where a ridiculously long device type can expand the field selector out too far
                var rdivwidth = $('div.right').outerWidth();
                $('div.right fieldset').each(function () {
                    rdivwidth = ($(this).outerWidth() > rdivwidth) ? $(this).outerWidth() : rdivwidth;
                });
                // offset for being centered
                rdivwidth = (rdivwidth > 495) ? (rdivwidth - 495) + rdivwidth : rdivwidth;
            } else {
                rdivwidth = 0;
            }

            var pnw = $('#pandn').outerWidth(), hw = $('#header').outerWidth(), maindiv = $('div.main').outerWidth(),
                    sbw = $('#sidebar').outerWidth(), width, mw = $('div.left').outerWidth() + rdivwidth + 20,
                    main, cw = $('.main > .center').outerWidth();
            widesttab += 58;

            // find widths
            width = (cw > mw) ? cw : mw;
            main = (pnw > width) ? pnw : width; // Find the largest width of possible content in maindiv
            main += 12; // add in padding and borders
            width = ((main + sbw) > hw) ? main + sbw : hw; // find the widest point of the page

            // The math just isn't adding up across browsers and FUCK IE
            if ((main + sbw) < width) { // page is larger than content expand main to fit
                $('#header').outerWidth(width);
                $('div.main').outerWidth(width - sbw - 4);
                $('div.page').outerWidth(width);
            } else { // page is smaller than content expand the page to fit
                $('div.main').width(width - sbw - 12);
                $('#header').width(width + 4);
                $('div.page').width(width + 6);
            }

            // If the function MoveButtons is defined run it
            if (typeof movebuttons == 'function') {
                movebuttons();
            }
        }
    }
    $(document).ready(function () {
        resize();
        // redraw the screen if the window size changes for some reason
        $(window).resize(function () {
            if (this.resizeTO) {
                clearTimeout(this.resizeTO);
            }
            this.resizeTO = setTimeout(function () {
                resize();
            }, 500);
        });
        $('#header').append($('.langselect'));
        $(".langselect").css({"right": "3px", "z-index": "99", "position": "absolute"}).removeClass('hide').appendTo("#header");
        $(".langselect").css({"bottom": $(".langselect").height() + "px"});
        $("#language").change(function () {
            $.ajax({
                type: 'POST',
                url: 'scripts/ajax_language.php',
                data: 'sl=' + $("#language").val(),
                success: function () {
                    // new cookie was set. reload the page for the translation.
                    location.reload();
                }
            });
        });
<?php
// No navigation menu if you're not logged in, yet
if (!strpos($_SERVER['SCRIPT_NAME'], "login")) {
    ?>
            $.get('scripts/ajax_navmenu.php').done(function (data) {
                $('#nav_placeholder').replaceWith(data);
                if (("#").readyState === "complete" && $('#datacenters .bullet').length == 0) {
                    window.convertTrees();
                }
            });
    <?php
}
?>
    });

</script>

<script type="text/javascript">
    var s = $('.searchname'),
            f = $('#formsearch'),
            a = $('.after'),
            m = $('h4');

    s.focus(function () {
        if (f.hasClass('open'))
            return;
        f.addClass('in');
        setTimeout(function () {
            f.addClass('open');
            f.removeClass('in');
        }, 1300);
    });

    a.on('click', function (e) {
        e.preventDefault();
        if (!f.hasClass('open'))
            return;
        s.val('');
        f.addClass('close');
        f.removeClass('open');
        setTimeout(function () {
            f.removeClass('close');
        }, 1300);
    })

</script>
