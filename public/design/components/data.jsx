
// ─── Utility ────────────────────────────────────────────────────────────────
const getGrade = v => v>=1000?'S':v>=800?'A':v>=600?'B':v>=400?'C':v>=200?'D':v>=100?'E':'F';
const STAT_COLORS  = { speed:'#E879A0', stamina:'#10B981', power:'#F59E0B', guts:'#EF4444', wit:'#3B82F6' };
const GRADE_COLORS = { S:'#F59E0B', A:'#E879A0', B:'#7C3AED', C:'#3B82F6', D:'#6B7280', E:'#9CA3AF', F:'#D1D5DB' };
const STAT_LABELS  = { speed:'Speed', stamina:'Stamina', power:'Power', guts:'Guts', wit:'Wit' };
const STAT_ICONS   = { speed:'⚡', stamina:'🌿', power:'🔥', guts:'❤️', wit:'💙' };

// ─── Shared UI primitives ────────────────────────────────────────────────────
function GradeBadge({ grade, size='sm' }) {
  const bg = GRADE_COLORS[grade] || '#9CA3AF';
  const px = size==='lg' ? '10px 16px' : '3px 9px';
  const fs = size==='lg' ? 18 : 11;
  return (
    <span style={{
      background: bg, color:'#fff', fontWeight:800,
      borderRadius:6, padding:px, fontSize:fs, letterSpacing:1,
      display:'inline-block', lineHeight:1.4,
    }}>{grade}</span>
  );
}

function StatBar({ stat, value }) {
  const color = STAT_COLORS[stat];
  const grade = getGrade(value);
  const pct   = Math.min(100, (value / 1200) * 100);
  const softCapPct = (1000/1200)*100;
  return (
    <div style={{ marginBottom:12 }}>
      <div style={{ display:'flex', justifyContent:'space-between', marginBottom:5, alignItems:'center' }}>
        <span style={{ fontSize:13, fontWeight:700, color:'#4A3570', display:'flex', alignItems:'center', gap:6 }}>
          <span>{STAT_ICONS[stat]}</span>{STAT_LABELS[stat]}
        </span>
        <div style={{ display:'flex', gap:6, alignItems:'center' }}>
          <GradeBadge grade={grade} />
          <span style={{ fontSize:14, fontWeight:800, color }}>{value}</span>
        </div>
      </div>
      <div style={{ height:8, background:'#EDE9FE', borderRadius:99, position:'relative', overflow:'hidden' }}>
        <div style={{
          height:'100%', width:`${pct}%`, borderRadius:99,
          background:`linear-gradient(90deg, ${color}99, ${color})`,
          transition:'width 0.6s cubic-bezier(.4,0,.2,1)'
        }}/>
        <div style={{ position:'absolute', top:0, left:`${softCapPct}%`, width:1, height:'100%', background:'rgba(124,58,237,0.25)' }}/>
      </div>
    </div>
  );
}

function MoodChip({ mood }) {
  const map = { Great:{bg:'#D1FAE5',color:'#065F46',icon:'✨'}, Good:{bg:'#EDE9FE',color:'#5B21B6',icon:'😊'}, Normal:{bg:'#F3F4F6',color:'#374151',icon:'😐'}, Bad:{bg:'#FEE2E2',color:'#991B1B',icon:'😟'} };
  const s = map[mood] || map.Normal;
  return <span style={{ background:s.bg, color:s.color, borderRadius:20, padding:'3px 10px', fontSize:12, fontWeight:700 }}>{s.icon} {mood}</span>;
}

function RarityBadge({ rarity }) {
  const map = { SSR:{bg:'linear-gradient(135deg,#F59E0B,#F97316)',color:'#fff'}, SR:{bg:'linear-gradient(135deg,#7C3AED,#A855F7)',color:'#fff'}, R:{bg:'#E5E7EB',color:'#374151'} };
  const s = map[rarity] || map.R;
  return <span style={{ background:s.bg, color:s.color, borderRadius:6, padding:'2px 8px', fontSize:11, fontWeight:800, letterSpacing:.5 }}>{rarity}</span>;
}

function Card({ children, style={}, className='' }) {
  return (
    <div className={className} style={{
      background:'#fff', borderRadius:16, border:'1px solid #EDE9FE',
      boxShadow:'0 2px 12px rgba(124,58,237,0.07)', ...style
    }}>{children}</div>
  );
}

function Btn({ children, variant='primary', size='md', onClick, style={}, disabled=false }) {
  const base = { borderRadius:10, fontWeight:700, cursor:disabled?'not-allowed':'pointer', border:'none', outline:'none', transition:'all .15s', fontFamily:'Nunito,sans-serif', display:'inline-flex', alignItems:'center', gap:6 };
  const sizes = { sm:{padding:'6px 14px',fontSize:12}, md:{padding:'10px 20px',fontSize:14}, lg:{padding:'13px 28px',fontSize:15} };
  const variants = {
    primary:{ background:'linear-gradient(135deg,#E879A0,#7C3AED)', color:'#fff', boxShadow:'0 4px 12px rgba(232,121,160,.35)' },
    secondary:{ background:'#EDE9FE', color:'#7C3AED' },
    ghost:{ background:'transparent', color:'#7C6FAB' },
    danger:{ background:'#FEE2E2', color:'#DC2626' },
    gold:{ background:'linear-gradient(135deg,#F59E0B,#F97316)', color:'#fff', boxShadow:'0 4px 12px rgba(245,158,11,.35)' },
  };
  return (
    <button onClick={onClick} disabled={disabled} style={{ ...base, ...sizes[size], ...variants[variant], opacity:disabled?.5:1, ...style }}>
      {children}
    </button>
  );
}

// ─── Characters data ──────────────────────────────────────────────────────────
const MOCK_CHARACTERS = [
  {
    id:1, name:'Mejiro Ardan', rarity:'SSR', scenario:'URA Finals',
    turn:45, totalTurns:78, stage:'Classic Year', mood:'Good', energy:78, condition:'Normal',
    stats:{ speed:520, stamina:480, power:440, guts:460, wit:450 },
    aptitudes:{ mile:'A', medium:'A', sprint:'B', long:'C', turf:'A', dirt:'B', lateSurger:'S', paceChaser:'A', frontRunner:'B', endCloser:'C' },
    factors:[{ stat:'speed',rating:3,bonus:21 },{ stat:'power',rating:2,bonus:12 },{ stat:'wit',rating:1,bonus:5 }],
    goals:[
      { id:1, label:'Speed ≥ 800', current:520, target:800, status:'on_track' },
      { id:2, label:'Stamina ≥ 600', current:480, target:600, status:'at_risk' },
      { id:3, label:'Win G1 Race', current:0, target:1, status:'on_track' },
    ],
    recentTurns:[
      { turn:44, action:'Speed Training', gains:['+48 Speed','+12 Power'], sp:15 },
      { turn:43, action:'Stamina Training', gains:['+42 Stamina','+8 Guts'], sp:12 },
      { turn:42, action:'Power Training', gains:['+35 Power','+10 Speed'], sp:10 },
    ],
    sp:450, totalSp:2400, usedSp:1950,
  },
  {
    id:2, name:'Kitasan Black', rarity:'SSR', scenario:'Unity Cup',
    turn:32, totalTurns:78, stage:'Classic Year', mood:'Great', energy:92, condition:'Good',
    stats:{ speed:480, stamina:560, power:520, guts:420, wit:390 },
    aptitudes:{ mile:'B', medium:'A', sprint:'C', long:'A', turf:'A', dirt:'C', lateSurger:'A', paceChaser:'S', frontRunner:'B', endCloser:'B' },
    factors:[{ stat:'stamina',rating:3,bonus:21 },{ stat:'guts',rating:2,bonus:12 }],
    goals:[{ id:1, label:'Stamina ≥ 900', current:560, target:900, status:'on_track' },{ id:2, label:'Win Unity Cup',current:0,target:1,status:'on_track' }],
    recentTurns:[{ turn:31, action:'Power Training', gains:['+45 Power','+8 Speed'], sp:14 }],
    sp:280, totalSp:1800, usedSp:1520,
  },
  {
    id:3, name:'Tokai Teio', rarity:'SSR', scenario:'URA Finals',
    turn:18, totalTurns:78, stage:'Junior Year', mood:'Normal', energy:65, condition:'Tired',
    stats:{ speed:320, stamina:380, power:290, guts:350, wit:310 },
    aptitudes:{ mile:'A', medium:'B', sprint:'A', long:'B', turf:'S', dirt:'D', lateSurger:'B', paceChaser:'A', frontRunner:'A', endCloser:'C' },
    factors:[{ stat:'speed',rating:2,bonus:12 },{ stat:'wit',rating:2,bonus:12 }],
    goals:[{ id:1, label:'Speed ≥ 500', current:320, target:500, status:'at_risk' }],
    recentTurns:[{ turn:17, action:'Wit Training', gains:['+38 Wit','+6 Guts'], sp:10 }],
    sp:120, totalSp:800, usedSp:680,
  },
];

const TRAINING_OPTIONS = [
  { id:'speed',   label:'Speed',   icon:'⚡', color:'#E879A0', bg:'#FDF2F8', gains:{ speed:52, power:14 }, risk:12, fatigue:15, sp:15, skills:['Speed Boost I'], recommended:true,  bonusNote:'Vodka bond: +8' },
  { id:'stamina', label:'Stamina', icon:'🌿', color:'#10B981', bg:'#F0FDF4', gains:{ stamina:48, guts:10 }, risk:8,  fatigue:12, sp:12, skills:[], recommended:false, bonusNote:null },
  { id:'power',   label:'Power',   icon:'🔥', color:'#F59E0B', bg:'#FFFBEB', gains:{ power:44, speed:12 },  risk:18, fatigue:20, sp:14, skills:['Power Surge'], recommended:false, bonusNote:'Kitasan bond: +12' },
  { id:'guts',    label:'Guts',    icon:'❤️', color:'#EF4444', bg:'#FFF5F5', gains:{ guts:46, stamina:8 },  risk:10, fatigue:10, sp:10, skills:[], recommended:false, bonusNote:null },
  { id:'wit',     label:'Wit',     icon:'💙', color:'#3B82F6', bg:'#EFF6FF', gains:{ wit:50, guts:6 },      risk:6,  fatigue:8,  sp:12, skills:['Wit Up I'], recommended:false, bonusNote:null },
];

const UPCOMING_RACES = [
  { id:1, name:'Mile Cup',           grade:'G2', distance:'Mile (1600m)',   surface:'Turf', weather:'Sunny',  turn:52, readiness:85, winProb:48, style:'Late Surger', required:false },
  { id:2, name:'Kanto Okami Cup',    grade:'G1', distance:'Medium (2400m)', surface:'Turf', weather:'Cloudy', turn:55, readiness:72, winProb:35, style:'Late Surger', required:true },
  { id:3, name:'Spring Tenno Sho',   grade:'G1', distance:'Long (3200m)',   surface:'Turf', weather:'Sunny',  turn:60, readiness:52, winProb:18, style:'Pace Chaser', required:false },
];

const SKILLS_DATA = [
  { id:1, name:"Predator's Instinct", type:'unique',   rarity:'unique', cost:180, owned:true,  hint:false, description:'Activates acceleration boost in final phase.' },
  { id:2, name:'Speed Boost I',       type:'speed',    rarity:'common', cost:90,  owned:true,  hint:false, description:'Small speed bonus activates in mid-race.' },
  { id:3, name:'Late Surger+',        type:'style',    rarity:'rare',   cost:120, owned:true,  hint:false, description:'Enhanced activation for late-race surge style.' },
  { id:4, name:'Recovery I',          type:'recovery', rarity:'common', cost:80,  owned:true,  hint:false, description:'Recovers stamina during the race.' },
  { id:5, name:'Mile Specialist',     type:'distance', rarity:'rare',   cost:100, owned:true,  hint:false, description:'Bonus activation in mile distance races.' },
  { id:6, name:'Power Surge',         type:'power',    rarity:'rare',   cost:110, owned:false, hint:true,  hintFrom:['Kitasan Black'], description:'Power burst during acceleration phase.' },
  { id:7, name:'Wit Up I',            type:'wit',      rarity:'common', cost:85,  owned:false, hint:false, description:'Improves skill activation reliability.' },
  { id:8, name:'Catch Up',            type:'recovery', rarity:'rare',   cost:130, owned:false, hint:false, description:'Gap recovery skill in early-mid race.' },
  { id:9, name:"Gold Ship's Spirit",  type:'unique',   rarity:'unique', cost:200, owned:false, hint:false, description:'Special skill from Gold Ship factor.' },
];

const SUPPORT_DECK = [
  { id:1, slot:1, name:'Kitasan Black', type:'Power',   rarity:'SSR', bond:85, tier:'S',  skills:['Power Surge','Guts Up I'] },
  { id:2, slot:2, name:'Vodka',         type:'Speed',   rarity:'SSR', bond:70, tier:'A',  skills:['Speed Boost I','Recovery I'] },
  { id:3, slot:3, name:'Gold Ship',     type:'Guts',    rarity:'SSR', bond:92, tier:'S+', skills:["Gold Ship's Spirit",'Guts Boost II'] },
  { id:4, slot:4, name:'Nice Nature',   type:'Stamina', rarity:'SR',  bond:60, tier:'B',  skills:['Stamina Up I'] },
  { id:5, slot:5, name:'Taiki Shuttle', type:'Speed',   rarity:'SR',  bond:55, tier:'B',  skills:['Mile Specialist'] },
  { id:6, slot:6, name:'Trainer',       type:'Friend',  rarity:'SSR', bond:78, tier:'A+', skills:['Motivation Up','Training Boost'] },
];

const AI_MESSAGES_INIT = [
  { role:'assistant', content:'Welcome back! **Mejiro Ardan** is on Turn 45 (Classic Year). Here\'s your quick brief:\n\n📊 **Status:** Good mood · 78% energy — solid for training\n\n🎯 **Priority:** Speed training recommended. You need +280 Speed to reach 800 by Turn 60, and gains are on track at ~52/turn.\n\n⚠️ **Watch:** Stamina is slightly behind target at 480/600. Consider 1–2 Stamina turns before Kanto Okami Cup.\n\nHow can I help you today?' },
  { role:'user', content:'Should I race the Mile Cup or skip it to keep training?' },
  { role:'assistant', content:'**Recommendation: Race in the Mile Cup ✅**\n\nHere\'s the breakdown:\n\n• **Readiness is 85%** — your best current race fit\n• **Win probability: 48%** — well above average\n• **SP gain:** A win earns ~180 SP, equivalent to 2–3 training turns\n• **Fan milestone:** You\'re 1,200 fans short of your Classic Year target; a G2 win closes it\n\nThe 1-turn training cost is worth it. I\'d race Mile Cup, then do 2× Speed + 1× Stamina before Kanto Okami.' },
];

const TRAINEES = [
  { id:1,  name:'Mejiro Ardan',    rarity:'SSR', type:'Speed',   scenario:'URA Finals' },
  { id:2,  name:'Kitasan Black',   rarity:'SSR', type:'Power',   scenario:'Unity Cup'  },
  { id:3,  name:'Tokai Teio',      rarity:'SSR', type:'Stamina', scenario:'URA Finals' },
  { id:4,  name:'Vodka',           rarity:'SSR', type:'Speed',   scenario:'URA Finals' },
  { id:5,  name:'Gold Ship',       rarity:'SSR', type:'Guts',    scenario:'URA Finals' },
  { id:6,  name:'Oguri Cap',       rarity:'SSR', type:'Stamina', scenario:'URA Finals' },
  { id:7,  name:'Silence Suzuka',  rarity:'SSR', type:'Speed',   scenario:'URA Finals' },
  { id:8,  name:'Special Week',    rarity:'SSR', type:'Stamina', scenario:'URA Finals' },
  { id:9,  name:'El Condor Pasa',  rarity:'SR',  type:'Speed',   scenario:'URA Finals' },
  { id:10, name:'Grass Wonder',    rarity:'SR',  type:'Wit',     scenario:'URA Finals' },
  { id:11, name:'Seiun Sky',       rarity:'SR',  type:'Wit',     scenario:'URA Finals' },
  { id:12, name:'Haru Urara',      rarity:'R',   type:'Guts',    scenario:'URA Finals' },
];

Object.assign(window, {
  getGrade, STAT_COLORS, GRADE_COLORS, STAT_LABELS, STAT_ICONS,
  GradeBadge, StatBar, MoodChip, RarityBadge, Card, Btn,
  MOCK_CHARACTERS, TRAINING_OPTIONS, UPCOMING_RACES, SKILLS_DATA, SUPPORT_DECK, AI_MESSAGES_INIT, TRAINEES,
});
