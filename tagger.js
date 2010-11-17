function iframeload() {
  var ifd = $($('#hgweb').get(0).contentDocument);
  ifd.find('.tag:contains(thtlive)').effect("pulsate", { times:7 }, 200);
  ifd.find('td.description,span.desc')
    .prepend('<button value="thtlive">thtlive</button>');
  ifd.find('button[value=thtlive]').click(thtlive);
}

function thtlive() {
  var href=$(this).parents('td.description,span.desc').find('a').attr('href');
  var rev = /[0-9a-f]+$/.exec(href)[0];
  location.search="?thtlive=" + rev;
}