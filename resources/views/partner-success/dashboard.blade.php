@extends('layouts.app')

@section('content')
<div id="ps-dash">
  <div class="ps-toolbar">
    <div class="ps-geo" id="ps-geo"></div>
    <select id="ps-partner"></select>
    <span class="ps-sum" id="ps-sum"></span>
  </div>
  <div class="ps-tabs" id="ps-tabs"></div>
  <h1 class="ps-h1">PSM Engagement</h1>
  <div class="ps-sub">Showing <b id="ps-scope">all partners</b> — every number is computed from the audited warehouse and drills to source. The GEO and PARTNER filters above re-run everything.</div>
  <div id="ps-cards" class="ps-cards"></div>
  <div id="ps-charts" class="ps-charts"></div>
  <div class="ps-hint">Click any number, bar, or segment → its source rows, the SQL behind it, and the weekly history.</div>

  <div id="ps-ov"></div>
  <div id="ps-dw"><div class="ps-dwh"><h2 id="ps-dwt"></h2><button class="ps-x" onclick="psHide()">×</button></div><div class="ps-body" id="ps-dwb"></div></div>
</div>

<style>
 #ps-dash{--accent:#e8600f;--head:#12263a;--navy:#0e2033;--mut:#8a94a0;--line:#e3e7ec;--ink:#1f2733;color:var(--ink)}
 #ps-dash .ps-toolbar{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:var(--head);color:#fff;padding:10px 16px;border-radius:10px}
 #ps-dash .ps-geo{display:flex;gap:4px}
 #ps-dash .ps-geo button{background:#22384c;color:#c6d2de;border:0;padding:5px 11px;border-radius:5px;cursor:pointer;font-size:12px}
 #ps-dash .ps-geo button.on{background:var(--accent);color:#fff}
 #ps-dash #ps-partner{background:#22384c;color:#e7eef4;border:1px solid #33465a;border-radius:5px;padding:5px 8px;font-size:12px;max-width:240px}
 #ps-dash .ps-sum{margin-left:auto;font-size:13px;color:#c6d2de} #ps-dash .ps-sum b{color:#fff} #ps-dash .ps-sum .o{color:var(--accent)}
 #ps-dash .ps-tabs{display:flex;gap:2px;border-bottom:1px solid var(--line);margin:14px 0 4px;flex-wrap:wrap}
 #ps-dash .ps-tab{color:var(--mut);padding:9px 15px;cursor:pointer;font-size:13px;border-bottom:3px solid transparent}
 #ps-dash .ps-tab.on{color:var(--head);border-bottom-color:var(--accent);font-weight:600}
 #ps-dash .ps-h1{font-size:22px;margin:10px 0 2px} #ps-dash .ps-sub{color:var(--mut);margin-bottom:16px;font-size:13px}
 #ps-dash .ps-cards{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
 @media(max-width:900px){#ps-dash .ps-cards{grid-template-columns:repeat(2,1fr)}}
 #ps-dash .ps-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px 16px 12px}
 #ps-dash .ps-card .t{font-size:11px;letter-spacing:.04em;color:var(--mut);text-transform:uppercase}
 #ps-dash .ps-num{font-size:25px;font-weight:700;margin-top:7px;cursor:pointer;border-bottom:2px dotted var(--mut);display:inline-block}
 #ps-dash .ps-num:hover{color:var(--accent);border-bottom-color:var(--accent)}
 #ps-dash .ps-card.accent .ps-num{color:var(--accent)} #ps-dash .ps-card .c{color:var(--mut);font-size:12px;margin-top:5px}
 #ps-dash .ps-na{font-size:11px;color:var(--mut);margin-top:5px;font-style:italic}
 #ps-dash .ps-spark{width:100%;height:28px;margin-top:8px;display:block;overflow:visible}
 #ps-dash .ps-spark polyline{fill:none;stroke:var(--mut);stroke-width:1.5} #ps-dash .ps-card.accent .ps-spark polyline{stroke:var(--accent)}
 #ps-dash .ps-spark circle{fill:var(--accent)}
 #ps-dash .ps-charts{display:grid;gap:14px;margin-top:16px}
 #ps-dash .ps-chart{background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px}
 #ps-dash .ps-chart h3{margin:0 0 10px;font-size:13px}
 #ps-dash svg.ps-bar{width:100%;max-width:560px;height:auto;display:block}
 #ps-dash .ps-bar rect{cursor:pointer} #ps-dash .ps-bar rect:hover{opacity:.8} #ps-dash .ps-bar text{font-size:10px;fill:var(--mut)}
 #ps-dash .ps-leg{font-size:12px} #ps-dash .ps-leg div{display:flex;align-items:center;gap:6px;margin:3px 0;cursor:pointer}
 #ps-dash .ps-leg .sw{width:10px;height:10px;border-radius:2px} #ps-dash .ps-leg .v{margin-left:auto;color:var(--mut)}
 #ps-dash .ps-donut path{cursor:pointer} #ps-dash .ps-donut path:hover{opacity:.85}
 #ps-dash .ps-hint{color:var(--mut);font-size:12px;margin-top:18px}
 #ps-ov{position:fixed;inset:0;background:rgba(10,20,30,.45);display:none;z-index:1040}
 #ps-dw{position:fixed;top:0;right:0;height:100%;width:min(960px,95vw);background:#fff;box-shadow:-8px 0 30px rgba(0,0,0,.25);display:none;z-index:1050;flex-direction:column}
 #ps-dw .ps-dwh{background:#12263a;color:#fff;padding:14px 18px;display:flex;align-items:center} #ps-dw .ps-dwh h2{margin:0;font-size:16px}
 #ps-dw .ps-x{margin-left:auto;cursor:pointer;color:#cdd7e0;font-size:22px;background:none;border:0}
 #ps-dw .ps-body{padding:16px 18px;overflow:auto}
 #ps-dash .ps-meta{background:#f6f8fa;border:1px solid var(--line);border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px}
 #ps-dash .ps-meta code{display:block;white-space:pre-wrap;background:#0d1b2a;color:#cfe3ff;padding:10px;border-radius:6px;margin-top:8px;font-size:12px;overflow:auto}
 #ps-dash .ps-prov{font-size:12px;color:#3a4a5a;margin:6px 0}
 #ps-dw table{border-collapse:collapse;width:100%;font-size:12px} #ps-dw th,#ps-dw td{border-bottom:1px solid var(--line);padding:6px 8px;text-align:left;vertical-align:top}
 #ps-dw th{position:sticky;top:0;background:#f0f3f6} #ps-dw td.p{color:#6b7683;background:#fafbfc} #ps-dw a{color:var(--accent)}
</style>

<script>
const PS_API = "{{ url('partner-success/api') }}";
const PS_PALETTE=['#12263a','#e8600f','#3b6ea5','#6c7a89','#5c59c6','#b33014','#123d57','#2e8b57'];
const PS_HEALTH={Green:'#2e8b57',Yellow:'#e0a800',Red:'#b33014',Missing:'#6c7a89','—':'#c3ccd4'};
const PS_CHARTS={
 Pipeline:[['By sales stage','stage','bar'],['By line of business','lob','donut'],['By region','geo','donut']],
 Delivery:[['By PS health','dhealth','bar'],['By region','dgeo','donut']],
 Capability:[['By capability group','capgrp','bar']],
 Enablement:[['By role','role','bar']],
 Catalog:[['Courses by source','csource','bar']]
};
const psCur=n=>{n=+n;if(!n)return '$0';if(n>=1e9)return '$'+(n/1e9).toFixed(2)+'B';if(n>=1e6)return '$'+(n/1e6).toFixed(1)+'M';if(n>=1e3)return '$'+(n/1e3).toFixed(0)+'K';return '$'+n};
const psNum=n=>(+n).toLocaleString();
const psEsc=s=>String(s).replace(/"/g,'&quot;');
let PS_METRICS=[], PS_TAB=null, PS_GEO='', PS_PARTNER='';
const psQp=()=>{const p=new URLSearchParams();if(PS_GEO)p.set('geo',PS_GEO);if(PS_PARTNER)p.set('partner',PS_PARTNER);const s=p.toString();return s?('?'+s):'';};
const psQp2=()=>{const p=new URLSearchParams();if(PS_GEO)p.set('geo',PS_GEO);if(PS_PARTNER)p.set('partner',PS_PARTNER);const s=p.toString();return s?('&'+s):'';};

async function psBoot(){
 const gb=document.getElementById('ps-geo');
 gb.innerHTML=['All','AMER','APAC','EMEA'].map(g=>`<button data-g="${g==='All'?'':g}">${g}</button>`).join('');
 gb.querySelectorAll('button').forEach(b=>b.onclick=()=>{PS_GEO=b.dataset.g;psRefresh();});
 const ps=await (await fetch(PS_API+'/partners')).json();
 const sel=document.getElementById('ps-partner');
 sel.innerHTML='<option value="">All partners ('+ps.length+')</option>'+ps.map(p=>`<option>${p.replace(/</g,'&lt;')}</option>`).join('');
 sel.onchange=()=>{PS_PARTNER=sel.value;psRefresh();};
 await psRefresh(true);
}
async function psRefresh(first){
 document.querySelectorAll('#ps-geo button').forEach(b=>b.classList.toggle('on',b.dataset.g===PS_GEO));
 PS_METRICS=await (await fetch(PS_API+'/metrics'+psQp())).json();
 if(first){
   const secs=[...new Set(PS_METRICS.map(m=>m.section))];
   const t=document.getElementById('ps-tabs'); t.innerHTML='';
   secs.forEach(s=>{const d=document.createElement('div');d.className='ps-tab';d.textContent=s;d.onclick=()=>psSelect(s);t.appendChild(d);});
   PS_TAB=secs[0];
 }
 const g=k=>PS_METRICS.find(m=>m.name===k)||{};
 document.getElementById('ps-sum').innerHTML=`<b>${psNum(g('total_opps').count||0)}</b> opps · <span class=o>${psCur(g('open_pipe').acv)}</span> open · <b>${psCur(g('closed_won').acv)}</b> won`;
 const scopeEl=document.getElementById('ps-scope');
 if(scopeEl) scopeEl.textContent=(PS_PARTNER||'all partners')+(PS_GEO?(' · '+PS_GEO):'');
 psSelect(PS_TAB);
}
function psSelect(s){
 PS_TAB=s;
 document.querySelectorAll('.ps-tab').forEach(x=>x.classList.toggle('on',x.textContent===s));
 psCards(s); psRenderCharts(s);
}
function psCards(s){
 const box=document.getElementById('ps-cards'); box.innerHTML='';
 PS_METRICS.filter(m=>m.section===s).forEach(x=>{
   const cur=x.unit==='currency', val=cur?psCur(x.acv):psNum(x.count);
   const sub=cur?(psNum(x.count)+' opps'):(x.acv?psCur(x.acv)+' ACV':'&nbsp;');
   const el=document.createElement('div'); el.className='ps-card'+(x.name==='open_pipe'?' accent':'');
   let na=[]; if(PS_GEO&&!x.geo_applies)na.push('GEO'); if(PS_PARTNER&&!x.partner_applies)na.push('partner');
   el.innerHTML=`<div class=t>${x.title}</div><div class=ps-num data-m="${x.name}">${val}</div><div class=c>${sub}</div>`+
     (na.length?`<div class=ps-na>${na.join(' & ')} filter n/a here</div>`:'')+
     `<svg class=ps-spark data-m="${x.name}" viewBox="0 0 120 28" preserveAspectRatio=none></svg>`;
   box.appendChild(el);
 });
 box.querySelectorAll('.ps-num').forEach(n=>n.onclick=()=>psShowMetric(n.dataset.m));
 box.querySelectorAll('.ps-spark').forEach(psSpark);
}
async function psSpark(svg){
 const d=await (await fetch(PS_API+'/trend/'+svg.dataset.m+psQp())).json();
 const pts=d.points; if(pts.length<2)return;
 const cur=d.unit==='currency', vals=pts.map(p=>cur?+p.acv:+p.count);
 const mn=Math.min(...vals),mx=Math.max(...vals),rng=(mx-mn)||1;
 const X=i=>i/(pts.length-1)*118+1, Y=v=>25-((v-mn)/rng)*22, last=vals.length-1;
 svg.innerHTML=`<polyline points="${vals.map((v,i)=>X(i).toFixed(1)+','+Y(v).toFixed(1)).join(' ')}"/><circle cx="${X(last).toFixed(1)}" cy="${Y(vals[last]).toFixed(1)}" r="2.4"/>`;
}
async function psRenderCharts(s){
 const wrap=document.getElementById('ps-charts'); const specs=PS_CHARTS[s]||[];
 wrap.style.gridTemplateColumns=`repeat(${Math.max(1,specs.length)},1fr)`;
 wrap.innerHTML=specs.map((c,i)=>`<div class=ps-chart><h3>${c[0]}</h3><div id=ps-ch${i}></div></div>`).join('');
 for(let i=0;i<specs.length;i++){const [t,key,type]=specs[i];
   const data=await (await fetch(PS_API+'/breakdown/'+key+psQp())).json();
   (type==='bar'?psBar:psDonut)('ps-ch'+i,data,key);}
}
function psColor(key,i,dim){ if(dim==='dhealth'&&PS_HEALTH[key])return PS_HEALTH[key]; return dim==='dhealth'?PS_PALETTE[i%PS_PALETTE.length]:PS_PALETTE[0]; }
function psBar(id,data,dim){
 const W=360,H=190,pad=28,gap=(W-pad*2)/Math.max(1,data.length),bw=gap*0.6;
 const useAcv=data.some(d=>+d.acv>0), mx=Math.max(...data.map(d=>useAcv?+d.acv:+d.count))||1;
 let svg=`<svg class=ps-bar viewBox="0 0 ${W} ${H}">`;
 data.forEach((d,i)=>{const val=useAcv?+d.acv:+d.count,h=val/mx*(H-pad*2),x=pad+i*gap+(gap-bw)/2,y=H-pad-h;
   svg+=`<rect x=${x.toFixed(1)} y=${y.toFixed(1)} width=${bw.toFixed(1)} height=${Math.max(0,h).toFixed(1)} rx=2 fill="${psColor(d.key,i,dim)}" data-v="${psEsc(d.key)}"><title>${d.key}: ${useAcv?psCur(d.acv):psNum(d.count)}</title></rect>`;
   svg+=`<text x=${(x+bw/2).toFixed(1)} y=${H-pad+12} text-anchor=middle>${String(d.key).split('.')[0].slice(0,11)}</text>`;});
 svg+=`</svg>`;
 const el=document.getElementById(id); el.innerHTML=svg;
 el.querySelectorAll('rect').forEach(r=>r.onclick=()=>psShowBk(dim,r.dataset.v));
}
function psDonut(id,data,dim){
 const useAcv=data.some(d=>+d.acv>0), tot=data.reduce((s,d)=>s+(useAcv?+d.acv:+d.count),0)||1;
 const R=70,r=42,cx=80,cy=80; let a=-Math.PI/2,svg=`<svg class=ps-donut viewBox="0 0 160 160" width=160 height=160>`,legend=[];
 data.forEach((d,i)=>{let frac=(useAcv?+d.acv:+d.count)/tot; if(frac>=0.999)frac=0.9999;
   const a2=a+frac*2*Math.PI,col=(dim==='dhealth'&&PS_HEALTH[d.key])?PS_HEALTH[d.key]:PS_PALETTE[i%PS_PALETTE.length];
   const p=(ang,rad)=>[cx+rad*Math.cos(ang),cy+rad*Math.sin(ang)];
   const[x1,y1]=p(a,R),[x2,y2]=p(a2,R),[x3,y3]=p(a2,r),[x4,y4]=p(a,r),lg=frac>0.5?1:0;
   svg+=`<path d="M${x1} ${y1} A${R} ${R} 0 ${lg} 1 ${x2} ${y2} L${x3} ${y3} A${r} ${r} 0 ${lg} 0 ${x4} ${y4} Z" fill="${col}" data-v="${psEsc(d.key)}"><title>${d.key}: ${useAcv?psCur(d.acv):psNum(d.count)}</title></path>`;
   legend.push(`<div data-v="${psEsc(d.key)}"><span class=sw style="background:${col}"></span>${d.key}<span class=v>${useAcv?psCur(d.acv):psNum(d.count)}</span></div>`); a=a2;});
 svg+=`</svg>`;
 const el=document.getElementById(id);
 el.innerHTML=`<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">${svg}<div class=ps-leg style=flex:1>${legend.join('')}</div></div>`;
 el.querySelectorAll('[data-v]').forEach(n=>n.onclick=()=>psShowBk(dim,n.dataset.v));
}
async function psShowMetric(name){
 const d=await (await fetch(PS_API+'/drill/'+name+psQp())).json();
 const tr=await (await fetch(PS_API+'/trend/'+name+psQp())).json();
 psDrawer(d,tr);
}
async function psShowBk(dim,value){
 const d=await (await fetch(PS_API+'/drill-breakdown/'+dim+'?value='+encodeURIComponent(value)+psQp2())).json();
 psDrawer(d,{points:[]});
}
function psDrawer(d,tr){
 document.getElementById('ps-dwt').textContent=d.title+' — '+d.row_count+' rows';
 const prov=d.provenance.map(p=>`load run #${p.load_run_id} · ${p.source_file} · snapshot ${p.snapshot_date}`).join('<br>')||'—';
 let hist='';
 if(tr.points&&tr.points.length){const cur=tr.unit==='currency';
   hist='<div class=ps-prov>History (one row per weekly snapshot):</div><table><thead><tr><th>snapshot</th><th>value</th></tr></thead><tbody>'+
     tr.points.map(p=>`<tr><td>${p.d}</td><td>${cur?psCur(p.acv):psNum(p.count)}</td></tr>`).join('')+'</tbody></table>';}
 const cols=d.columns, pcols=new Set(['snapshot_date','load_run_id','source_file','source_id']);
 const head='<tr>'+cols.map(c=>`<th>${c}</th>`).join('')+'</tr>';
 const body=d.rows.slice(0,500).map(r=>'<tr>'+cols.map(c=>{let v=r[c]??'';
    if(c==='source_id'&&v&&String(v).startsWith('http'))v=`<a href="${v}" target=_blank>${String(v).split('/').pop()}</a>`;
    return `<td class="${pcols.has(c)?'p':''}">${v}</td>`}).join('')+'</tr>').join('');
 document.getElementById('ps-dwb').innerHTML=
   `<div class=ps-meta><b>${d.description}</b><div class=ps-prov>Source: ${prov}</div>
     <div class=ps-prov>Definition (SQL behind this number):</div><code>${d.definition_sql.replace(/</g,'&lt;')}</code>${hist}</div>
    <div style="overflow:auto"><table><thead>${head}</thead><tbody>${body}</tbody></table></div>
    ${d.row_count>500?'<div class=ps-prov>Showing first 500 of '+d.row_count+'.</div>':''}`;
 document.getElementById('ps-ov').style.display='block'; document.getElementById('ps-dw').style.display='flex';
}
function psHide(){document.getElementById('ps-ov').style.display='none';document.getElementById('ps-dw').style.display='none';}
document.getElementById('ps-ov').addEventListener('click',psHide);
psBoot();
</script>
@endsection
