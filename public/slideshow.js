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
