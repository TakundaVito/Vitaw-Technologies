(()=>{
document.querySelector('#year').textContent=new Date().getFullYear();
const menu=document.querySelector('.menu-toggle');menu?.addEventListener('click',()=>{const open=menu.getAttribute('aria-expanded')!=='true';menu?.setAttribute('aria-expanded',String(open));document.querySelector('nav').classList.toggle('open',open)});document.querySelectorAll('nav a').forEach(a=>a.addEventListener('click',()=>{menu?.setAttribute('aria-expanded','false');document.querySelector('nav').classList.remove('open')}));document.addEventListener('keydown',e=>{if(e.key==='Escape'){menu?.setAttribute('aria-expanded','false');document.querySelector('nav').classList.remove('open')}});
const textEsc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const portfolioProjects=[{"kind": "projects", "slug": "nheyotech", "title": "Nheyotech Technical College", "category": "Software", "summary": "A website for a technical and vocational training institution in Zimbabwe.", "url": "https://nheyotech.ac.zw/", "sector": "Education"}, {"kind": "projects", "slug": "carstie", "title": "Carstie Technical College", "category": "Software", "summary": "An online presence for an engineering and skills development centre.", "url": "https://carstie.co.zw/", "sector": "Education"}, {"kind": "projects", "slug": "vaima", "title": "Vaima Car Wash & Detailing", "category": "Software", "summary": "A business website presenting car wash, automotive detailing and valet services.", "url": "https://vaima.co.zw/", "sector": "Automotive services"}, {"kind": "projects", "slug": "chase-the-transporter", "title": "Chase The Transporter", "category": "Software", "summary": "A website connecting customers with courier and logistics services.", "url": "https://chasethetransporter.co.zw/", "sector": "Logistics"}, {"kind": "projects", "slug": "eastgate-furnitures", "title": "Eastgate Furnitures", "category": "Software", "summary": "A product showcase and enquiry experience for a Zimbabwean furniture business.", "url": "https://eastgatefurnitures.co.zw/", "sector": "Furniture"}, {"kind": "projects", "slug": "engirvine", "title": "Engirvine", "category": "Software", "summary": "A personal portfolio presenting an electronic engineer’s work across automation, networks and cybersecurity.", "url": "https://engirvine.co.zw/", "sector": "Engineering portfolio"}, {"kind": "projects", "slug": "kudzai-vito", "title": "Kudzai Vito", "category": "Software", "summary": "A personal website presenting Kudzai’s work and digital presence.", "url": "https://kudzai.vito.co.zw/", "sector": "Personal portfolio"}];
let liveProjects=[...portfolioProjects];
function renderProjects(category='All'){
 const list=document.querySelector('#project-list');const rows=liveProjects.filter(p=>category==='All'||p.category===category);
 if(!liveProjects.length)return;
 list.innerHTML=rows.length?rows.map(p=>`<article class="service"><span class="mono">${textEsc(p.category)}</span><h3>${textEsc(p.title)}</h3><p>${textEsc(p.summary)}</p><a href="/projects/${encodeURIComponent(p.slug)}/">View project ↗</a></article>`).join(''):'<p>No published projects in this discipline yet.</p>';
 list.classList.toggle('service-grid',rows.length>0);list.classList.remove('empty-state');
}
document.querySelectorAll('[data-filter]').forEach(b=>b.addEventListener('click',()=>renderProjects(b.dataset.filter)));
fetch('/api/content.php', {cache:'no-store'}).then(r=>r.json()).then(data=>{
 if(data.setupPending){document.querySelector('#inquiry button').disabled=true;document.querySelector('#form-status').textContent='The inquiry form is being configured. Please email us or use WhatsApp to discuss your project.';}
 const published=(Array.isArray(data.entries)?data.entries:[]).filter(e=>e.kind==='projects');liveProjects=[...published,...portfolioProjects.filter(p=>!published.some(e=>e.slug===p.slug))];renderProjects();
 const articles=(Array.isArray(data.entries)?data.entries:[]).filter(e=>e.kind==='insights').slice(0,3);if(articles.length){const box=document.querySelector('#insights .empty-state');box.className='service-grid';box.innerHTML=articles.map(p=>`<article class="service"><span class="mono">${textEsc(p.category)}</span><h3>${textEsc(p.title)}</h3><p>${textEsc(p.summary)}</p><a href="/insights/${encodeURIComponent(p.slug)}/">Read insight ↗</a></article>`).join('')}
 const s=data.settings||{};if(s.location)document.querySelector('.contact .mono').textContent='SERVING CLIENTS WORLDWIDE · HEADQUARTERED IN '+s.location.toUpperCase();if(s.heroHeadline)document.querySelector('h1').textContent=s.heroHeadline;if(s.heroDescription)document.querySelector('.hero-copy').textContent=s.heroDescription;
 if(s.email&&/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s.email))document.querySelectorAll('a[href^="mailto:"]').forEach(a=>{a.href='mailto:'+s.email;if(a.classList.contains('contact-link'))a.textContent=s.email+' ↗'});
 if(s.phone&&/^\+?[\d\s-]{6,25}$/.test(s.phone)){document.querySelectorAll('a[href^="tel:"]').forEach(a=>{a.href='tel:'+s.phone.replace(/\s/g,'');a.textContent=s.phone});document.querySelectorAll('a[href^="https://wa.me/"]').forEach(a=>a.href='https://wa.me/'+s.phone.replace(/\D/g,''))}
 const stats=[['projectsDelivered','PROJECTS DELIVERED'],['technologies','TECHNOLOGIES'],['disciplines','ENGINEERING DISCIPLINES'],['clients','CLIENTS']].filter(([key])=>/^\d+$/.test(s[key]||''));if(stats.length){const div=document.createElement('div');div.className='pipeline';div.innerHTML=stats.map(([key,label])=>`<div><strong style="font-size:30px;color:#0066ff">${textEsc(s[key])}</strong><br><span class="mono">${label}</span></div>`).join('');document.querySelector('.why').append(div)}
}).catch(()=>{});
document.querySelector('#inquiry').addEventListener('submit',async e=>{e.preventDefault();const f=e.currentTarget,status=document.querySelector('#form-status'),button=f.querySelector('button');const file=f.attachment.files[0];if(file&&file.size>5*1024*1024){status.textContent='Please select an attachment smaller than 5 MB.';return}button.disabled=true;status.textContent='Sending your inquiry…';try{const r=await fetch(f.action,{method:'POST',body:new FormData(f)});let data;try{data=await r.json()}catch{throw Error('The inquiry service is temporarily unavailable.')}if(!r.ok)throw Error(data.error||'Your inquiry could not be sent.');status.textContent='Thank you. Your inquiry has been received.';f.reset()}catch(err){status.textContent=`${err.message} Please email info@vitaw.co.zw or use WhatsApp.`}finally{button.disabled=false}});


})();
(()=>{
 const carousel=document.querySelector('.tech-slideshow');if(!carousel)return;
 const slides=[...carousel.querySelectorAll('.tech-slide')],dots=[...carousel.querySelectorAll('[data-slide]')];
 const play=carousel.querySelector('#slide-play'),counter=carousel.querySelector('#slide-counter'),announcement=carousel.querySelector('#slide-announcement');
 const motion=matchMedia('(prefers-reduced-motion: reduce)'),connection=navigator.connection;
 let index=0,userPaused=motion.matches||Boolean(connection?.saveData)||['slow-2g','2g'].includes(connection?.effectiveType),hovered=false,focused=false,visible=true,timer,request=0;
 const label=()=>{play.setAttribute('aria-label',userPaused?'Play slideshow':'Pause slideshow');play.setAttribute('aria-pressed',String(userPaused));play.firstElementChild.textContent=userPaused?'▶':'Ⅱ'};
 async function loadImage(i){
  const img=slides[i].querySelector('img');if(!img.dataset.src&&img.complete)return img.naturalWidth>0;
  const ready=new Promise(resolve=>{img.addEventListener('load',()=>resolve(true),{once:true});img.addEventListener('error',()=>resolve(false),{once:true})});
  if(img.dataset.src){img.loading='eager';if(img.dataset.srcset)img.srcset=img.dataset.srcset;img.src=img.dataset.src;delete img.dataset.src;delete img.dataset.srcset;}
  return img.complete?img.naturalWidth>0:ready;
 }
 function schedule(){clearTimeout(timer);if(!userPaused&&!hovered&&!focused&&visible&&!document.hidden)timer=setTimeout(async()=>{await show(index+1,false);schedule()},7000)}
 async function show(next,user=true){
  clearTimeout(timer);const target=(next+slides.length)%slides.length,token=++request;
  const loaded=await loadImage(target);if(token!==request)return;
  if(!loaded){if(user)announcement.textContent='This image could not load. Please try another slide.';userPaused=true;label();return;}
  index=target;slides.forEach((s,i)=>{s.classList.toggle('is-active',i===index);s.setAttribute('aria-hidden',String(i!==index))});dots.forEach((d,i)=>{d.classList.toggle('active',i===index);d.setAttribute('aria-pressed',String(i===index))});counter.textContent=`0${index+1} — 0${slides.length}`;
  if(user){announcement.textContent=slides[index].getAttribute('aria-label');schedule()}
 }
 carousel.querySelector('#slide-prev').addEventListener('click',()=>show(index-1));carousel.querySelector('#slide-next').addEventListener('click',()=>show(index+1));dots.forEach(d=>d.addEventListener('click',()=>show(Number(d.dataset.slide))));
 play.addEventListener('click',()=>{userPaused=!userPaused;label();schedule()});
 carousel.addEventListener('pointerenter',()=>{hovered=true;schedule()});carousel.addEventListener('pointerleave',()=>{hovered=false;schedule()});
 carousel.addEventListener('focusin',()=>{focused=true;schedule()});carousel.addEventListener('focusout',e=>{focused=carousel.contains(e.relatedTarget);schedule()});
 carousel.addEventListener('keydown',e=>{if(e.key==='ArrowRight'){e.preventDefault();show(index+1)}if(e.key==='ArrowLeft'){e.preventDefault();show(index-1)}});
 document.addEventListener('visibilitychange',schedule);motion.addEventListener('change',e=>{if(e.matches)userPaused=true;label();schedule()});
 if('IntersectionObserver' in window)new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;schedule()},{threshold:.1}).observe(carousel);
 label();schedule();
})();
