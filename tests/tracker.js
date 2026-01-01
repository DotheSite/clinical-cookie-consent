// Simple tracker script used by blocking-test.html
(function(){
  var el = document.createElement('div');
  el.id = 'ccc-tracker-status';
  el.style.cssText = 'position:fixed;right:12px;top:12px;background:#111;color:#fff;padding:8px;border-radius:4px;z-index:99999';
  el.textContent = 'Tracker executed at ' + new Date().toLocaleTimeString();
  document.body.appendChild(el);
  console.log('Tracker script executed');
})();
