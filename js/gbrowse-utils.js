function loadGbrowse(dest, selector, data, callback) {
  // load gbrowse content into dest, given gbrowse's data
  if (!selector)
    selector = " #overview_panel";
  return $j(dest).load('/perl/gbrowse/tht/' + selector, data, callback);
}
