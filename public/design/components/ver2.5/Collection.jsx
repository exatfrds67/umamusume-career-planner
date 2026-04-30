
// ── Skill Loadout Manager (WF-009) ────────────────────────────────────────────
const MAX_SLOTS = 10;

const LOADOUT_SKILLS = [
  { id:1,  name:"Predator's Instinct", type:'unique',  rarity:'unique',  cost:180, hintLevel:0, activation:'Final Spurt Phase', owned:true,  active:true  },
  { id:2,  name:'Speed Boost I',       type:'speed',   rarity:'normal',  cost:90,  hintLevel:2, activation:'Middle Phase',      owned:true,  active:true  },
  { id:3,  name:'Late Surger+',        type:'style',   rarity:'rare',    cost:120, hintLevel:3, activation:'Final Stretch',     owned:true,  active:true  },
  { id:4,  name:'Recovery I',          type:'recovery',rarity:'normal',  cost:80,  hintLevel:1, activation:'HP < 40%',          owned:true,  active:true  },
  { id:5,  name:'Mile Specialist',     type:'distance',rarity:'rare',    cost:100, hintLevel:5, activation:'Mile Distance',     owned:true,  active:true  },
  { id:6,  name:'Wit Up I',            type:'wit',     rarity:'normal',  cost:85,  hintLevel:0, activation:'Skill Phase',       owned:true,  active:false },
  { id:7,  name:'Catch Up',            type:'recovery',rarity:'rare',    cost:130, hintLevel:2, activation:'Back of Pack',      owned:true,  active:false },
  { id:8,  name:'Power Surge',         type:'power',   rarity:'rare',    cost:110, hintLevel:1, activation:'Corner Entry',      owned:true,  active:false },
  { id:9,  name:'Guts Up I',           type:'guts',    rarity:'normal',  cost:75,  hintLevel:4, activation:'Final Phase',       owned:true,  active:false },
  { id:10, name:'Stamina Saver',       type:'stamina', rarity:'normal',  cost:70,  hintLevel:0, activation:'Opening Phase',    owned:true,  active:false },
];

const HINT_DISCOUNTS = [0,10,20,30,35,40];
const discountedCost = (sk) => Math.round(sk.cost * (1 - (HINT_DISCOUNTS[sk.hintLevel]||0)/100));

const PRESETS = [
  { id:1, name:'G1 Sprint',   ids:[1,2,3,4,5,6,7,8], score:88 },
  { id:2, name:'Long Distance', ids:[1,4,5,9,10,3,7,6], score:82 },
  { id:3, name:'Safe Farm',   ids:[2,4,6,9,10,7,3,5],  score:76 },
];

function SkillLoadout() {
  const char = MOCK_CHARACTERS[0];
  const [skills, setSkills] = React.useState(LOADOUT_SKILLS);
  const [selectedPreset, setSelectedPreset] = React.useState(null);
  const [aiSuggestion] = React.useState({ add:'Power Surge', reason:'Activates at corner entry — boosts Mile Cup win probability by ~8%.' });

  const typeColor = { unique:'#7C3AED', speed:'#E879A0', stamina:'#10B981', power:'#F97316', guts:'#EF4444', wit:'#3B82F6', style:'#8B5CF6', distance:'#06B6D4', recovery:'#10B981' };
  const typeIcon  = { unique:'⭐', speed:'⚡', stamina:'🌿', power:'🔥', guts:'❤️', wit:'💙', style:'🎯', distance:'🏁', recovery:'💊' };
  const rarityColor = { unique:'#7C3AED', rare:'#F59E0B', normal:'#6B7280' };
  const rarityLabel = { unique:'Unique', rare:'Rare', normal:'Normal' };

  const activeSkills = skills.filter(s=>s.active);
  const totalCost = activeSkills.reduce((s,sk)=>s+discountedCost(sk),0);
  const score = Math.min(100, Math.round(activeSkills.length * 9.2 + activeSkills.filter(s=>s.rarity==='rare'||s.rarity==='unique').length * 4));

  const toggleActive = (id) => {
    const sk = skills.find(s=>s.id===id);
    if (!sk.active && activeSkills.length >= MAX_SLOTS) return;
    setSkills(prev=>prev.map(s=>s.id===id?{...s,active:!s.active}:s));
  };

  const loadPreset = (preset) => {
    setSelectedPreset(preset.id);
    setSkills(prev=>prev.map(s=>({...s,active:preset.ids.includes(s.id)})));
  };

  const scoreColor = score>=90?'#10B981':score>=75?'#F59E0B':'#EF4444';
  const scoreLabel = score>=90?'S+':score>=80?'S':score>=70?'A':'B';

  const HintStars = ({ level }) => (
    <div style={{ display:'flex', gap:2 }}>
      {[0,1,2,3,4].map(i=>(
        <span key={i} style={{ fontSize:10, color:i<level?'#F59E0B':'#D1D5DB' }}>★</span>
      ))}
    </div>
  );

  // Synergy analysis
  const hasMileSpec = activeSkills.some(s=>s.name==='Mile Specialist');
  const hasLateSurger = activeSkills.some(s=>s.name==='Late Surger+');
  const synergies = [
    { active:hasMileSpec && hasLateSurger, label:'Mile + Late Surger combo', bonus:'+12% final stretch', color:'#10B981' },
    { active:activeSkills.filter(s=>s.type==='recovery').length>=2, label:'Double recovery', bonus:'Stamina safety net', color:'#3B82F6' },
    { active:activeSkills.some(s=>s.rarity==='unique'), label:'Unique skill active', bonus:'Character-specific boost', color:'#7C3AED' },
  ];

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Skill Loadout Manager</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{char.name} · Race build optimization</div>
        </div>
      </div>

      {/* Status bar */}
      <Card style={{ padding:16, marginBottom:20, background:'linear-gradient(135deg,#F5F3FF,#EDE9FE)', border:'1px solid #C4B5FD' }}>
        <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:14 }}>
          {[
            { label:'Active Skills', val:`${activeSkills.length}/${MAX_SLOTS}`, color:'#7C3AED' },
            { label:'Total Cost', val:`${totalCost} SP`, color:'#F59E0B' },
            { label:'Loadout Score', val:`${score} (${scoreLabel})`, color:scoreColor },
            { label:'Fast Learner', val:'✓ Active (+10%)', color:'#10B981' },
          ].map(item=>(
            <div key={item.label} style={{ textAlign:'center' }}>
              <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700, marginBottom:3 }}>{item.label}</div>
              <div style={{ fontSize:16, fontWeight:900, color:item.color }}>{item.val}</div>
            </div>
          ))}
        </div>
      </Card>

      {/* AI Suggestion */}
      <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', borderRadius:14, padding:'12px 18px', marginBottom:20, display:'flex', gap:12, alignItems:'center' }}>
        <span style={{ fontSize:22 }}>🤖</span>
        <div style={{ flex:1 }}>
          <div style={{ fontSize:12, fontWeight:800, color:'#F9A8D4', marginBottom:2 }}>AI Recommendation</div>
          <div style={{ fontSize:13, color:'rgba(255,255,255,0.8)' }}>Add <strong style={{color:'#FCD34D'}}>{aiSuggestion.add}</strong> — {aiSuggestion.reason}</div>
        </div>
        <Btn variant='secondary' size='sm' onClick={()=>toggleActive(8)}>Add Skill</Btn>
      </div>

      <div style={{ display:'grid', gridTemplateColumns:'1fr 280px', gap:20 }}>
        {/* Loadout grid */}
        <div>
          {/* Presets */}
          <div style={{ display:'flex', gap:8, marginBottom:16, alignItems:'center' }}>
            <span style={{ fontSize:12, fontWeight:800, color:'#7C6FAB' }}>PRESETS:</span>
            {PRESETS.map(p=>(
              <button key={p.id} onClick={()=>loadPreset(p)} style={{ padding:'6px 14px', borderRadius:8, border:`1px solid ${selectedPreset===p.id?'#C4B5FD':'#EDE9FE'}`, background:selectedPreset===p.id?'#EDE9FE':'#F9F5FF', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, color:selectedPreset===p.id?'#7C3AED':'#7C6FAB' }}>
                {p.name} <span style={{ fontSize:10, color:'#10B981' }}>({p.score})</span>
              </button>
            ))}
          </div>

          {/* Slot grid */}
          <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(220px,1fr))', gap:10 }}>
            {/* Active slots */}
            {Array.from({length:MAX_SLOTS}).map((_,i)=>{
              const sk = activeSkills[i];
              if (!sk) return (
                <div key={`empty-${i}`} style={{ borderRadius:12, border:'2px dashed #EDE9FE', padding:'16px', textAlign:'center', background:'#FAFBFF', minHeight:100, display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center', gap:6 }}>
                  <div style={{ fontSize:20, color:'#C4B5FD' }}>+</div>
                  <div style={{ fontSize:11, color:'#C4B5FD', fontWeight:700 }}>Slot {i+1} — Empty</div>
                  <div style={{ fontSize:10, color:'#D1D5DB' }}>Select from below</div>
                </div>
              );
              const tc = typeColor[sk.type]||'#7C3AED';
              const disc = HINT_DISCOUNTS[sk.hintLevel]||0;
              const finalCost = discountedCost(sk);
              return (
                <div key={sk.id} style={{ borderRadius:12, border:`2px solid ${tc}44`, background:'#fff', padding:'14px', position:'relative', overflow:'hidden' }}>
                  <div style={{ position:'absolute', top:0, left:0, right:0, height:3, background:tc }}/>
                  <div style={{ display:'flex', gap:8, alignItems:'flex-start', marginBottom:8 }}>
                    <div style={{ width:32, height:32, borderRadius:8, background:tc+'18', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16, flexShrink:0 }}>{typeIcon[sk.type]||'✨'}</div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontSize:12, fontWeight:800, color:'#1E1033', lineHeight:1.3 }}>{sk.name}</div>
                      <div style={{ display:'flex', gap:4, marginTop:3 }}>
                        <span style={{ fontSize:9, background:rarityColor[sk.rarity]+'22', color:rarityColor[sk.rarity], borderRadius:4, padding:'1px 5px', fontWeight:800 }}>{rarityLabel[sk.rarity]}</span>
                        <span style={{ fontSize:9, background:tc+'18', color:tc, borderRadius:4, padding:'1px 5px', fontWeight:700 }}>{sk.type}</span>
                      </div>
                    </div>
                    <button onClick={()=>toggleActive(sk.id)} style={{ background:'#FEE2E2', border:'none', borderRadius:6, width:22, height:22, cursor:'pointer', fontSize:12, color:'#EF4444', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>✕</button>
                  </div>
                  <div style={{ fontSize:10, color:'#7C6FAB', marginBottom:6 }}>⚡ {sk.activation}</div>
                  <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                    <HintStars level={sk.hintLevel}/>
                    <span style={{ fontSize:11, fontWeight:800, color:disc>0?'#10B981':'#F59E0B' }}>
                      {disc>0&&<span style={{ textDecoration:'line-through', color:'#9CA3AF', marginRight:4 }}>{sk.cost}</span>}
                      {finalCost} SP
                    </span>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Inactive owned skills */}
          <div style={{ marginTop:20 }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB', marginBottom:10 }}>OWNED — NOT IN LOADOUT</div>
            <div style={{ display:'flex', flexDirection:'column', gap:6 }}>
              {skills.filter(s=>!s.active).map(sk=>{
                const tc = typeColor[sk.type]||'#7C3AED';
                const disc = HINT_DISCOUNTS[sk.hintLevel]||0;
                const canAdd = activeSkills.length < MAX_SLOTS;
                return (
                  <div key={sk.id} style={{ display:'flex', gap:10, alignItems:'center', padding:'10px 14px', background:'#F9F5FF', borderRadius:10, border:'1px solid #EDE9FE', opacity:canAdd?1:.6 }}>
                    <span style={{ fontSize:16 }}>{typeIcon[sk.type]||'✨'}</span>
                    <div style={{ flex:1 }}>
                      <span style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>{sk.name}</span>
                      <div style={{ fontSize:10, color:'#7C6FAB' }}>{sk.activation}</div>
                    </div>
                    <HintStars level={sk.hintLevel}/>
                    <span style={{ fontSize:11, fontWeight:800, color:'#F59E0B', minWidth:50, textAlign:'right' }}>{discountedCost(sk)} SP</span>
                    <Btn variant='secondary' size='sm' disabled={!canAdd} onClick={()=>toggleActive(sk.id)}>+ Add</Btn>
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        {/* Sidebar: synergy + score */}
        <div style={{ display:'flex', flexDirection:'column', gap:14 }}>
          {/* Score */}
          <Card style={{ padding:20, textAlign:'center' }}>
            <div style={{ fontSize:12, fontWeight:700, color:'#7C6FAB', marginBottom:6 }}>LOADOUT SCORE</div>
            <div style={{ fontSize:56, fontWeight:900, color:scoreColor, lineHeight:1 }}>{score}</div>
            <div style={{ fontSize:20, fontWeight:900, color:scoreColor, marginBottom:10 }}>({scoreLabel})</div>
            <div style={{ height:8, background:'#EDE9FE', borderRadius:99 }}>
              <div style={{ height:'100%', width:`${score}%`, background:`linear-gradient(90deg,${scoreColor},${scoreColor}cc)`, borderRadius:99 }}/>
            </div>
          </Card>

          {/* Synergies */}
          <Card style={{ padding:18 }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:12 }}>Skill Synergies</div>
            {synergies.map((syn,i)=>(
              <div key={i} style={{ display:'flex', gap:8, alignItems:'center', padding:'8px 10px', background:syn.active?syn.color+'12':'#F9F5FF', borderRadius:8, marginBottom:6, border:`1px solid ${syn.active?syn.color+'33':'#EDE9FE'}` }}>
                <span style={{ fontSize:14 }}>{syn.active?'✅':'⬜'}</span>
                <div style={{ flex:1 }}>
                  <div style={{ fontSize:11, fontWeight:700, color:syn.active?'#1E1033':'#9CA3AF' }}>{syn.label}</div>
                  <div style={{ fontSize:10, color:syn.active?syn.color:'#D1D5DB', fontWeight:700 }}>{syn.bonus}</div>
                </div>
              </div>
            ))}
          </Card>

          {/* Coverage */}
          <Card style={{ padding:18 }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:12 }}>Coverage</div>
            {Object.entries(typeColor).filter(([t])=>['speed','stamina','power','guts','wit'].includes(t)).map(([type,color])=>{
              const count = activeSkills.filter(s=>s.type===type).length;
              return (
                <div key={type} style={{ display:'flex', alignItems:'center', gap:8, marginBottom:6 }}>
                  <span style={{ fontSize:13 }}>{typeIcon[type]}</span>
                  <span style={{ fontSize:12, color:'#7C6FAB', flex:1, textTransform:'capitalize' }}>{type}</span>
                  <div style={{ display:'flex', gap:3 }}>
                    {[0,1,2].map(i=><div key={i} style={{ width:12, height:12, borderRadius:3, background:i<count?color:'#EDE9FE' }}/>)}
                  </div>
                </div>
              );
            })}
          </Card>
        </div>
      </div>
    </div>
  );
}

// ── Support Card Collection (WF-010) ──────────────────────────────────────────
const CARD_COLLECTION = [
  { id:1,  name:'Kitasan Black',   type:'Power',   rarity:'SSR', tier:'SS',  lb:4, bond:100, fb:35, inDeck:true  },
  { id:2,  name:'Vodka',           type:'Speed',   rarity:'SSR', tier:'S+',  lb:2, bond:70,  fb:28, inDeck:true  },
  { id:3,  name:'Gold Ship',       type:'Guts',    rarity:'SSR', tier:'S',   lb:4, bond:92,  fb:30, inDeck:true  },
  { id:4,  name:'Nice Nature',     type:'Stamina', rarity:'SR',  tier:'B',   lb:0, bond:60,  fb:18, inDeck:true  },
  { id:5,  name:'Taiki Shuttle',   type:'Speed',   rarity:'SR',  tier:'B',   lb:1, bond:55,  fb:20, inDeck:true  },
  { id:6,  name:'Trainer',         type:'Friend',  rarity:'SSR', tier:'A+',  lb:3, bond:78,  fb:32, inDeck:true  },
  { id:7,  name:'Seiun Sky',       type:'Wit',     rarity:'SR',  tier:'A',   lb:2, bond:45,  fb:18, inDeck:false },
  { id:8,  name:'Haru Urara',      type:'Guts',    rarity:'R',   tier:'C',   lb:0, bond:12,  fb:12, inDeck:false },
  { id:9,  name:'Special Week',    type:'Stamina', rarity:'SSR', tier:'S+',  lb:1, bond:88,  fb:29, inDeck:false },
  { id:10, name:'El Condor Pasa',  type:'Speed',   rarity:'SR',  tier:'A',   lb:0, bond:33,  fb:16, inDeck:false },
  { id:11, name:'Grass Wonder',    type:'Wit',     rarity:'SR',  tier:'B',   lb:2, bond:65,  fb:20, inDeck:false },
  { id:12, name:'Oguri Cap',       type:'Stamina', rarity:'SSR', tier:'A+',  lb:0, bond:22,  fb:26, inDeck:false },
  { id:13, name:'Silence Suzuka',  type:'Speed',   rarity:'SSR', tier:'S',   lb:3, bond:78,  fb:30, inDeck:false },
  { id:14, name:'Tokai Teio',      type:'Power',   rarity:'SSR', tier:'A',   lb:1, bond:48,  fb:27, inDeck:false },
  { id:15, name:'Mejiro McQueen',  type:'Stamina', rarity:'SSR', tier:'S',   lb:2, bond:55,  fb:28, inDeck:false },
  { id:16, name:'Narita Brian',    type:'Guts',    rarity:'SR',  tier:'B',   lb:0, bond:8,   fb:15, inDeck:false },
];

function SupportCardCollection() {
  const [typeFilter, setTypeFilter] = React.useState('ALL');
  const [rarityFilter, setRarityFilter] = React.useState('ALL');
  const [bondFilter, setBondFilter] = React.useState('ALL');
  const [sortBy, setSortBy] = React.useState('tier');
  const [view, setView] = React.useState('grid');
  const [selected, setSelected] = React.useState(null);
  const [page, setPage] = React.useState(1);
  const PER_PAGE = 8;

  const typeColor = { Speed:'#E879A0', Stamina:'#10B981', Power:'#F59E0B', Guts:'#EF4444', Wit:'#3B82F6', Friend:'#7C3AED' };
  const typeIcon  = { Speed:'⚡', Stamina:'🌿', Power:'🔥', Guts:'❤️', Wit:'💙', Friend:'🤝' };
  const tierOrder = { SS:0, 'S+':1, S:2, 'A+':3, A:4, B:5, C:6 };
  const tierBg    = { SS:'linear-gradient(135deg,#F59E0B,#EF4444)', 'S+':'linear-gradient(135deg,#F59E0B,#F97316)', S:'linear-gradient(135deg,#10B981,#059669)', 'A+':'linear-gradient(135deg,#E879A0,#7C3AED)', A:'linear-gradient(135deg,#C4B5FD,#7C3AED)', B:'#9CA3AF', C:'#D1D5DB' };

  const filtered = CARD_COLLECTION
    .filter(c => typeFilter==='ALL' || c.type===typeFilter)
    .filter(c => rarityFilter==='ALL' || c.rarity===rarityFilter)
    .filter(c => bondFilter==='ALL' || (bondFilter==='≥80%' ? c.bond>=80 : c.bond<80))
    .sort((a,b) => sortBy==='tier' ? (tierOrder[a.tier]||9)-(tierOrder[b.tier]||9) : sortBy==='bond' ? b.bond-a.bond : a.name.localeCompare(b.name));

  const totalPages = Math.ceil(filtered.length/PER_PAGE);
  const paged = filtered.slice((page-1)*PER_PAGE, page*PER_PAGE);

  const LBStars = ({ lb }) => (
    <div style={{ display:'flex', gap:2 }}>
      {[0,1,2,3,4].map(i=>(
        <span key={i} style={{ fontSize:10, color:i<lb+1?'#F59E0B':'#EDE9FE' }}>★</span>
      ))}
      {lb>=4 && <span style={{ fontSize:9, fontWeight:800, color:'#F59E0B', marginLeft:2 }}>MLB</span>}
    </div>
  );

  const FTStatus = ({ bond }) => bond >= 80
    ? <span style={{ fontSize:10, fontWeight:800, color:'#10B981', background:'#D1FAE5', borderRadius:6, padding:'1px 7px' }}>🤝 READY</span>
    : <span style={{ fontSize:10, fontWeight:700, color:'#7C6FAB', background:'#F9F5FF', borderRadius:6, padding:'1px 7px' }}>⏳ {Math.round(bond/80*100)}%</span>;

  const summary = {
    total: CARD_COLLECTION.length,
    ssr: CARD_COLLECTION.filter(c=>c.rarity==='SSR').length,
    avgBond: Math.round(CARD_COLLECTION.reduce((s,c)=>s+c.bond,0)/CARD_COLLECTION.length),
    mlb: CARD_COLLECTION.filter(c=>c.lb>=4).length,
  };

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Support Card Collection</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>Full catalog · Meta tier rankings · Bond tracking</div>
        </div>
        <Btn variant='secondary' size='sm'>🔄 Sync Meta</Btn>
      </div>

      {/* Summary bar */}
      <Card style={{ padding:16, marginBottom:20, background:'linear-gradient(135deg,#F5F3FF,#EDE9FE)', border:'1px solid #C4B5FD' }}>
        <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:14, textAlign:'center' }}>
          {[
            { label:'Total Cards', val:summary.total, color:'#7C3AED' },
            { label:`SSR (${Math.round(summary.ssr/summary.total*100)}%)`, val:summary.ssr, color:'#F59E0B' },
            { label:'Avg Bond', val:`${summary.avgBond}%`, color:'#E879A0' },
            { label:'Max LB', val:summary.mlb, color:'#10B981' },
          ].map(item=>(
            <div key={item.label}>
              <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>{item.label}</div>
              <div style={{ fontSize:24, fontWeight:900, color:item.color }}>{item.val}</div>
            </div>
          ))}
        </div>
      </Card>

      {/* Filters */}
      <div style={{ display:'flex', gap:8, marginBottom:20, flexWrap:'wrap', alignItems:'center' }}>
        <div style={{ display:'flex', gap:4 }}>
          {['ALL','Speed','Stamina','Power','Guts','Wit','Friend'].map(t=>(
            <button key={t} onClick={()=>{ setTypeFilter(t); setPage(1); }} style={{ padding:'6px 10px', borderRadius:8, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, background:typeFilter===t?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:typeFilter===t?'#fff':'#7C6FAB' }}>
              {t==='ALL'?'All':typeIcon[t]||''} {t!=='ALL'?t:''}
            </button>
          ))}
        </div>
        <div style={{ display:'flex', gap:4 }}>
          {['ALL','R','SR','SSR'].map(r=>(
            <button key={r} onClick={()=>{ setRarityFilter(r); setPage(1); }} style={{ padding:'6px 10px', borderRadius:8, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, background:rarityFilter===r?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:rarityFilter===r?'#fff':'#7C6FAB' }}>{r}</button>
          ))}
        </div>
        <div style={{ display:'flex', gap:4 }}>
          {['ALL','≥80%','<80%'].map(b=>(
            <button key={b} onClick={()=>{ setBondFilter(b); setPage(1); }} style={{ padding:'6px 10px', borderRadius:8, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, background:bondFilter===b?'linear-gradient(135deg,#10B981,#059669)':'#EDE9FE', color:bondFilter===b?'#fff':'#7C6FAB' }}>{b==='ALL'?'All Bond':b}</button>
          ))}
        </div>
        <div style={{ marginLeft:'auto', display:'flex', gap:6 }}>
          <select value={sortBy} onChange={e=>setSortBy(e.target.value)} style={{ padding:'6px 10px', borderRadius:8, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, color:'#7C3AED', background:'#F9F5FF', cursor:'pointer', outline:'none' }}>
            <option value='tier'>Sort: Meta Tier</option>
            <option value='bond'>Sort: Bond %</option>
            <option value='name'>Sort: Name</option>
          </select>
          {['grid','list'].map(v=>(
            <button key={v} onClick={()=>setView(v)} style={{ padding:'6px 12px', borderRadius:8, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, background:view===v?'#EDE9FE':'#F9F5FF', color:view===v?'#7C3AED':'#7C6FAB' }}>{v==='grid'?'⊞ Grid':'☰ List'}</button>
          ))}
        </div>
      </div>

      {/* Cards */}
      {view==='grid' ? (
        <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(170px,1fr))', gap:14, marginBottom:20 }}>
          {paged.map(card=>{
            const tc = typeColor[card.type]||'#7C3AED';
            const sel = selected?.id===card.id;
            return (
              <div key={card.id} onClick={()=>setSelected(sel?null:card)} style={{ borderRadius:14, border:`2px solid ${sel?tc:'transparent'}`, background:'#fff', overflow:'hidden', cursor:'pointer', boxShadow:'0 2px 8px rgba(124,58,237,0.07)', transition:'all .15s' }}>
                <div style={{ background:`linear-gradient(135deg,${tc}22,${tc}44)`, padding:'12px 12px 8px', position:'relative' }}>
                  <div style={{ position:'absolute', top:8, right:8 }}>
                    <span style={{ fontSize:10, fontWeight:900, color:'#fff', background:tierBg[card.tier]||'#EDE9FE', borderRadius:5, padding:'1px 6px' }}>{card.tier}</span>
                  </div>
                  <div style={{ fontSize:24, marginBottom:3 }}>{typeIcon[card.type]||'🃏'}</div>
                  <div style={{ fontSize:12, fontWeight:800, color:'#1E1033', lineHeight:1.3 }}>{card.name}</div>
                  <div style={{ display:'flex', gap:4, marginTop:4, alignItems:'center' }}>
                    <RarityBadge rarity={card.rarity}/>
                    {card.inDeck && <span style={{ fontSize:9, background:'#EDE9FE', color:'#7C3AED', borderRadius:4, padding:'1px 5px', fontWeight:800 }}>In Deck</span>}
                  </div>
                </div>
                <div style={{ padding:'10px 12px' }}>
                  <LBStars lb={card.lb}/>
                  <div style={{ marginTop:8 }}>
                    <div style={{ display:'flex', justifyContent:'space-between', fontSize:10, marginBottom:3 }}>
                      <span style={{ color:'#7C6FAB', fontWeight:700 }}>Bond</span>
                      <span style={{ fontWeight:900, color:card.bond>=80?'#10B981':tc }}>{card.bond}%</span>
                    </div>
                    <div style={{ height:5, background:'#EDE9FE', borderRadius:99, marginBottom:6 }}>
                      <div style={{ height:'100%', width:`${card.bond}%`, background:card.bond>=80?'#10B981':tc, borderRadius:99 }}/>
                    </div>
                  </div>
                  <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                    <FTStatus bond={card.bond}/>
                    <span style={{ fontSize:10, fontWeight:700, color:'#7C6FAB' }}>FB: +{card.fb}%</span>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      ) : (
        <div style={{ marginBottom:20 }}>
          {paged.map(card=>{
            const tc = typeColor[card.type]||'#7C3AED';
            return (
              <div key={card.id} onClick={()=>setSelected(selected?.id===card.id?null:card)} style={{ display:'flex', gap:14, alignItems:'center', padding:'12px 16px', background:'#fff', borderRadius:12, marginBottom:8, border:`1px solid ${selected?.id===card.id?tc:'#EDE9FE'}`, cursor:'pointer' }}>
                <div style={{ width:40, height:40, borderRadius:10, background:`linear-gradient(135deg,${tc}22,${tc}44)`, display:'flex', alignItems:'center', justifyContent:'center', fontSize:20, flexShrink:0 }}>{typeIcon[card.type]||'🃏'}</div>
                <div style={{ flex:1 }}>
                  <div style={{ display:'flex', gap:8, alignItems:'center' }}>
                    <span style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>{card.name}</span>
                    <span style={{ fontSize:10, fontWeight:900, color:'#fff', background:tierBg[card.tier]||'#EDE9FE', borderRadius:5, padding:'1px 7px' }}>{card.tier}</span>
                    <RarityBadge rarity={card.rarity}/>
                    {card.inDeck && <span style={{ fontSize:9, background:'#EDE9FE', color:'#7C3AED', borderRadius:4, padding:'1px 5px', fontWeight:800 }}>In Deck</span>}
                  </div>
                  <LBStars lb={card.lb}/>
                </div>
                <div style={{ display:'flex', gap:16, alignItems:'center' }}>
                  <div style={{ textAlign:'center' }}>
                    <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>BOND</div>
                    <div style={{ fontSize:15, fontWeight:900, color:card.bond>=80?'#10B981':tc }}>{card.bond}%</div>
                  </div>
                  <div style={{ textAlign:'center' }}>
                    <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>FB</div>
                    <div style={{ fontSize:14, fontWeight:800, color:'#7C6FAB' }}>+{card.fb}%</div>
                  </div>
                  <FTStatus bond={card.bond}/>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Pagination */}
      <div style={{ display:'flex', gap:8, justifyContent:'center', alignItems:'center' }}>
        <Btn variant='ghost' size='sm' disabled={page<=1} onClick={()=>setPage(p=>p-1)}>← Prev</Btn>
        <span style={{ fontSize:13, fontWeight:700, color:'#7C6FAB' }}>Page {page} / {totalPages || 1}</span>
        <Btn variant='ghost' size='sm' disabled={page>=totalPages} onClick={()=>setPage(p=>p+1)}>Next →</Btn>
      </div>
    </div>
  );
}

Object.assign(window, { SkillLoadout, SupportCardCollection });
