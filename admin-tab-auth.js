(function(){
  const key='resumeflow_admin_tab_token';
  const params=new URLSearchParams(location.search);
  const urlToken=params.get('admin_tab');
  let token=urlToken || sessionStorage.getItem(key);
  if(!token){
    const a=new Uint8Array(24); crypto.getRandomValues(a);
    token=Array.from(a,b=>b.toString(16).padStart(2,'0')).join('');
  }
  sessionStorage.setItem(key,token);
  const hidden=document.getElementById('adminTabToken');
  if(hidden) hidden.value=token;
  document.querySelectorAll('form').forEach(f=>{
    const existing=f.querySelector('input[name="admin_tab"]');
    if(existing) existing.value=token;
    else { const h=document.createElement('input'); h.type='hidden'; h.name='admin_tab'; h.value=token; f.appendChild(h); }
  });
  document.querySelectorAll('a[href]').forEach(a=>{
    const raw=a.getAttribute('href');
    if(!raw || raw.startsWith('#') || raw.startsWith('http') || raw.startsWith('mailto:') || raw.startsWith('javascript:')) return;
    if(raw.includes('admin_tab=')) return;
    const sep=raw.includes('?')?'&':'?';
    a.setAttribute('href',raw+sep+'admin_tab='+encodeURIComponent(token));
  });
})();
