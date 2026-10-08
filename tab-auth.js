(function(){
  const key='resumeflow_tab_token';
  const params=new URLSearchParams(location.search);
  const urlToken=params.get('tab');
  let token=urlToken || sessionStorage.getItem(key);
  if(!token){
    const a=new Uint8Array(24); crypto.getRandomValues(a);
    token=Array.from(a,b=>b.toString(16).padStart(2,'0')).join('');
  }
  sessionStorage.setItem(key,token);
  const hidden=document.getElementById('tabToken');
  if(hidden) hidden.value=token;
  document.querySelectorAll('form').forEach(f=>{
    const existing=f.querySelector('input[name="tab"]');
    if(existing) existing.value=token;
    else { const h=document.createElement('input'); h.type='hidden'; h.name='tab'; h.value=token; f.appendChild(h); }
  });
  document.querySelectorAll('a[href]').forEach(a=>{
    const raw=a.getAttribute('href');
    if(!raw || raw.startsWith('#') || raw.startsWith('http') || raw.startsWith('mailto:') || raw.startsWith('javascript:')) return;
    if(raw.includes('admin.php') || raw.includes('admin_preview.php') || raw.includes('tab=')) return;
    const sep=raw.includes('?')?'&':'?';
    a.setAttribute('href',raw+sep+'tab='+encodeURIComponent(token));
  });
})();

// Touch support: tap a menu trigger once to reveal its popup; normal links still navigate on the next tap.
document.querySelectorAll('.nav-trigger, .action-pop > a').forEach(function(el){
  el.addEventListener('touchstart', function(){
    const parent=el.closest('.nav-pop,.action-pop');
    if(parent){
      document.querySelectorAll('.nav-pop.is-open,.action-pop.is-open').forEach(function(x){if(x!==parent)x.classList.remove('is-open')});
      parent.classList.toggle('is-open');
    }
  }, {passive:true});
});
