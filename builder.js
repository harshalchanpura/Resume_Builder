const form=document.getElementById('resumeForm'), sheet=document.getElementById('liveResume');
const get=n=>form.querySelector(`[name="${n}"]`)?.value||'';
const escapeHtml=s=>(s||'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
function bulletList(s, splitComma=false){ const raw=(s||'').replace(/\r\n?/g,'\n').split('\n').flatMap(x=>splitComma?x.split(','):[x]).map(x=>x.trim().replace(/^[•\-–—*]+\s*/u,'')).filter(Boolean); return raw.length?`<ul class="resume-bullets" aria-label="Resume bullet points">${raw.map(x=>`<li>${escapeHtml(x)}</li>`).join('')}</ul>`:''; }
function nl(s){return escapeHtml(s).replace(/\n/g,'<br>')}
function render(){
 const accent=get('accent')||'#d97706', region=get('region')||'india', layout=get('layout')||'split', name=get('full_name')||'YOUR NAME', head=get('headline')||'Professional headline', target=get('target_job')||'Target role';
 const font=get('font_family')||'DM Sans', fontSize=get('font_size')||'10.5', sidebarFontSize=get('sidebar_font_size')||fontSize, mainFontSize=get('main_font_size')||fontSize, fontWeight=get('font_weight')||'400';
 sheet.style.setProperty('--accent',accent);
 sheet.style.setProperty('--resume-font',font);
 sheet.style.setProperty('--resume-size',fontSize+'pt');
 sheet.style.setProperty('--sidebar-size',sidebarFontSize+'pt');
 sheet.style.setProperty('--main-size',mainFontSize+'pt');
 sheet.style.setProperty('--resume-weight',fontWeight);

 const photo=form.querySelector('[name="photo"]')?.files?.[0]; const savedPhoto=sheet.dataset.photo||''; let photoHtml=''; if(photo){ photoHtml=`<div class="live-photo"><img src="${URL.createObjectURL(photo)}" alt="Profile photo"></div>`; } else if(savedPhoto){ photoHtml=`<div class="live-photo"><img src="${escapeHtml(savedPhoto)}" alt="Profile photo"></div>`; }
 const sections=[['CAREER OBJECTIVE','career_objective'],['EXPERIENCE','experience'],['PROJECTS','projects'],['ACADEMIC QUALIFICATION','academic_qualification'],['SKILLS','skills'],['CERTIFICATIONS','certifications'],['ACHIEVEMENTS','achievements'],['LANGUAGES','languages'],['HOBBIES','hobbies']];
 const body=sections.map(([t,k])=>get(k).trim()?`<section class="resume-section"><h2>${t}</h2><div class="resume-text">${['career_objective','experience','projects','skills','languages','hobbies'].includes(k)?bulletList(get(k),k==='skills'):nl(get(k))}</div></section>`:'').join('');
 const linkNames={linkedin:'LinkedIn',github:'GitHub',portfolio:'Portfolio'};
 const links=['linkedin','github','portfolio'].map(k=>{const url=get(k).trim(); if(!url)return ''; const href=/^https?:\/\//i.test(url)?url:'https://'+url; const displayUrl=url.replace(/[?#].*$/,''); return `<a class="resume-link" href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer"><strong>${linkNames[k]}:</strong> <span>${escapeHtml(displayUrl)}</span></a>`;}).join('');
 if(layout==='split'){const splitSections=[['EXPERIENCE','experience'],['PROJECTS','projects'],['ACADEMIC QUALIFICATION','academic_qualification'],['CERTIFICATIONS','certifications'],['ACHIEVEMENTS','achievements']]; const splitBody=splitSections.map(([t,k])=>get(k).trim()?`<section class="resume-section"><h2>${t}</h2><div class="resume-text">${nl(get(k))}</div></section>`:'').join(''); sheet.className=`resume-sheet ${layout} region-${region}`;sheet.innerHTML=`<aside class="resume-side">${photoHtml}<section><h4>CONTACT</h4>${bulletList([get('email'),get('phone'),get('location'),get('address')].filter(Boolean).join('\n'))}</section><section><h4>SKILLS</h4>${bulletList(get('skills')||'Add your core skills',true)}</section><section><h4>LANGUAGES</h4>${bulletList(get('languages'))}</section><section><h4>HOBBIES</h4>${bulletList(get('hobbies'))}</section></aside><article class="resume-main"><header class="resume-header split-header"><div><h1>${escapeHtml(name)}</h1><p>${escapeHtml(head)}</p><span>${escapeHtml(target)}</span></div></header><section class="resume-section split-profile"><h2>PROFILE</h2><div class="resume-text">${bulletList(get('career_objective'))}</div></section>${splitBody}<div class="resume-links">${links}</div></article>`;
 }else{
  sheet.className=`resume-sheet ${layout} region-${region}`;
  const layoutLabel={
    clean:'PROFESSIONAL RESUME',
    header:'PROFESSIONAL PROFILE',
    modern:'MODERN PROFESSIONAL',
    executive:'EXECUTIVE PROFILE',
    minimal:'RESUME',
    timeline:'CAREER TIMELINE',
    creative:'SELECTED PROFILE',
    corporate:'PROFESSIONAL RESUME',
    consulting:'CONSULTING PROFILE',
    tech:'TECHNICAL PROFILE',
    academic:'ACADEMIC CURRICULUM VITAE',
    compact:'ATS-READY RESUME',
    elegant:'PROFESSIONAL PROFILE',
    grid:'PROFESSIONAL PROFILE',
    mono:'RESUME',
    senior:'LEADERSHIP PROFILE'
  }[layout]||'PROFESSIONAL RESUME';
  sheet.innerHTML=`<article class="resume-main">
    <header class="resume-header">${photoHtml}<div><h1>${escapeHtml(name)}</h1><p>${escapeHtml(head)}</p><span>${escapeHtml(get('email'))} · ${escapeHtml(get('phone'))} · ${escapeHtml(get('location'))}</span></div></header>
    <div class="resume-top"><span>${layoutLabel} · ${escapeHtml(region.toUpperCase())} · ${escapeHtml(target)}</span></div>
    ${body}<div class="resume-links">${links}</div>
  </article>`;
 }
}
const layoutSelect=form.querySelector('[name="layout"]');
const fontSizeControl=form.querySelector('#fontSizeControls');
const singleFontControl=form.querySelector('#singleFontControl');
const sidebarFontControl=form.querySelector('#sidebarFontControl');
const mainFontControl=form.querySelector('#mainFontControl');
const fontSizeInput=form.querySelector('#font_size');
const sidebarFontInput=form.querySelector('#sidebar_font_size');
const mainFontInput=form.querySelector('#main_font_size');

function syncFontSizeControls(){
  const isSplit=(layoutSelect?.value||'split')==='split';
  if(fontSizeControl) fontSizeControl.classList.toggle('font-controls-single',!isSplit);
  if(singleFontControl) singleFontControl.hidden=isSplit;
  if(sidebarFontControl) sidebarFontControl.hidden=!isSplit;
  if(mainFontControl) mainFontControl.hidden=!isSplit;

  // When switching layout, carry the current main font size into the
  // single-column control, and carry the single size into both sides
  // when returning to Classic Sidebar.
  if(!isSplit && fontSizeInput && mainFontInput && document.activeElement===layoutSelect){
    fontSizeInput.value=mainFontInput.value;
  }
  if(isSplit && sidebarFontInput && mainFontInput && fontSizeInput && document.activeElement===layoutSelect){
    sidebarFontInput.value=fontSizeInput.value;
    mainFontInput.value=fontSizeInput.value;
  }
  render();
}

form.querySelectorAll('[data-live],select').forEach(x=>x.addEventListener('input',render));
if(layoutSelect) layoutSelect.addEventListener('change',syncFontSizeControls);
const photoInput=form.querySelector('[name="photo"]');
if(photoInput) photoInput.addEventListener('change',render);
const color=document.getElementById('accent'), text=document.getElementById('accentText'); if(color){ color.addEventListener('input',()=>{ text.value=color.value; render(); }); color.addEventListener('change',()=>{ text.value=color.value; render(); }); } if(text){ text.addEventListener('input',()=>{ if(/^#[0-9a-fA-F]{6}$/.test(text.value)){ color.value=text.value; render(); } }); }
syncFontSizeControls();
