
phantom.injectJs( getProjectPath() + '/casperjs/config/config.js');
var casper = require('casper').create({
    viewportSize: {
        width: 1600,
        height: 900
    },
    logLevel: "info",
    verbose: true
});
var url = casper.cli.raw.get('url') || 'https://www.pinterest.com/login/';

// ================================================================================
// Event
// ================================================================================

// 指定你要下載的檔案
casper.on('resource.received', function (resource) {
    if ( resource.stage != "end" ) {
        return;
    }

    var config = getConfig();
    var findString = config.adsId + "/export";
    if ((resource.url.indexOf(findString) !== -1) ) {
        echo('==== download resource: ' + resource.url );
        var file = getDownloadfileName();
        if ( !file ) {
            echo('==== ERROR: 無法正確建立下載的檔案!');
            return;
        }

        try {
            var pathFile = getProjectPath() + '/tmp/' + file
            echo('==== save to file: ' + pathFile );
            casper.download(resource.url, pathFile );
        } catch (e) {
            echo('Error: ');
            echo(e.message);
        }
    }
});


// ================================================================================
// Start
// ================================================================================

casper.start(url, function() {
    var config = getConfig();
    this.capture( getProjectPath() + "/tmp/url-before.png");
    echoInfo(this);
});

casper.then(function() {
    this.thenEvaluate(function(config) {
        $('input[name="username_or_email"]').val( config.account );
        $('input[name="password"]').val( config.password );
        $('form button[type="submit"]').click();
    }, getConfig() );
});

// redirect to
casper.thenOpen('https://ads.pinterest.com/');

// download csv file
casper.then(function() {
    echo('---- Export CSV ----');
    var config = getConfig();
    var from = getThreeDayAgo();
    var to   = getToday();
    casper.thenOpen('https://ads.pinterest.com/analytics/advertiser/'+ config.adsId +'/export/?start_date='+ from +'&end_date='+ to );
});

casper.run(function() {
    echoInfo(this);
    var config = getConfig();
    this.capture( getProjectPath() + "/tmp/url-after.png", {
        top: 0, left: 0, width: 1600, height: 900
    });
    this
        .echo('==== The End ====')
        .exit();
});




/* --------------------------------------------------------------------------------

-------------------------------------------------------------------------------- */
function getProjectPath()
{
    return '/var/www/kenshoo';
}

function echo(data)
{
    var type = Object.prototype.toString.call(data);
    switch (type) {
        case '[object String]':
            console.log(data);
            break;

        case '[object Array]':
            var items = [];
            for( key in data ) {
                items.push( data[key] );
            }
            content = '[' + items.join(",") + ']';
            console.log(content);
            break;

        case '[object Object]':
            var items = [];
            for( key in data ) {
                items.push( key +'='+ data[key] );
            }
            content = '{'+ items.join(",") +'}';
            console.log(content);
            break;

        default:
            console.log(type);
    }

    //console.log(data);
    //this.echo(data);
}

function echoInfo(that)
{
    echo('{');
    echo('    title=' + that.getTitle() );
    echo('    url=' + that.getCurrentUrl() );
    echo('}');
}

/**
 *  列出物件所有的 keys
 *
 *  example:
 *      dumpObjectKeys(this);
 */
function dumpObjectKeys(object)
{
    var keys = [];
    for( hash in object ) {
        keys.push(hash);
    }
    console.log( keys.join(",") );
}

/**
 *  get formate date
 *  yyyy-mm-dd
 */
function getToday()
{
    var nowDate = new Date();
    return formatDate(nowDate);
}

/**
 *  get formate date
 *  yyyy-mm-dd
 */

function getThreeDayAgo()
{
    var nowDate = new Date();
    var dDay = new Date(nowDate);
    dDay.setDate(nowDate.getDate() - 3);
    return formatDate(dDay);
}

function formatDate( theDate )
{
    try {
        var yyyy = theDate.getFullYear().toString();
        var mm = (theDate.getMonth()+1).toString();
        var dd = theDate.getDate().toString();
        var mmChars = mm.split('');
        var ddChars = dd.split('');
        return yyyy + '-' + (mmChars[1]?mm:"0"+mmChars[0]) + '-' + (ddChars[1]?dd:"0"+ddChars[0]);
    } catch (e) {
        echo('Error: ');
        echo(e.message);
    }
    return false;
}

// today date format
function getDownloadfileName()
{
    var from = getThreeDayAgo();
    var to   = getToday();
    if ( !from || !to ) {
        return "pinterest-undefined.csv";
    }
    return "pinterest-["+ from +"]-to-["+ to +"].csv";
}

