
const ACHIEVEMENTS_DATA = [
  // Stats
  { id:1,  cat:'stats',  name:'A-Grade Stat',        desc:'Reach A grade (800+) in any single stat',        icon:'📊', unlocked:false, progress:520,  target:800,    reward:'50 SP',       rewardSP:50  },
  { id:2,  cat:'stats',  name:'Total Stats Bronze',   desc:'Reach 3,000 combined stats',                    icon:'🥉', unlocked:false, progress:2350, target:3000,   reward:'100 SP',      rewardSP:100 },
  { id:3,  cat:'stats',  name:'Total Stats Silver',   desc:'Reach 4,000 combined stats',                    icon:'🥈', unlocked:false, progress:2350, target:4000,   reward:'200 SP',      rewardSP:200 },
  { id:4,  cat:'stats',  name:'Soft Cap Reached',     desc:'Reach 1,200 in any single stat',                icon:'💎', unlocked:false, progress:520,  target:1200,   reward:'500 SP',      rewardSP:500 },
  { id:5,  cat:'stats',  name:'Exceptional Stat',     desc:'Push any stat past 1,600 (deep diminishing returns zone)', icon:'🌟', unlocked:false, progress:520, target:1600, reward:'1,000 SP', rewardSP:1000 },
  // Races
  { id:6,  cat:'races',  name:'First Victory',        desc:'Win your first race',                            icon:'🏆', unlocked:true,  unlockedAt:'Turn 24', reward:'80 SP',  rewardSP:80  },
  { id:7,  cat:'races',  name:'G1 Champion',          desc:'Win a Grade 1 race',                             icon:'👑', unlocked:false, progress:0,    target:1,      reward:'300 SP',      rewardSP:300 },
  { id:8,  cat:'races',  name:'Triple Crown',         desc:'Win 3 specific G1 championship races',           icon:'🌸', unlocked:false, progress:0,    target:3,      reward:'1,000 SP',    rewardSP:1000},
  { id:9,  cat:'races',  name:'Undefeated Streak',    desc:'Win 5 consecutive races',                        icon:'🔥', unlocked:false, progress:1,    target:5,      reward:'250 SP',      rewardSP:250 },
  // Fan Class
  { id:10, cat:'fans',   name:'First Steps',          desc:'First Win — debut class achieved',               icon:'⭐', unlocked:true,  unlockedAt:'Turn 24', reward:'Debut Badge',  rewardSP:0   },
  { id:11, cat:'fans',   name:'Bronze Class',         desc:'Reach 5,000 fans',                               icon:'🥉', unlocked:true,  unlockedAt:'Turn 35', fans:5000,   reward:'Bronze Frame', rewardSP:0 },
  { id:12, cat:'fans',   name:'Silver Class',         desc:'Reach 20,000 fans',                              icon:'🥈', unlocked:false, progress:8200, target:20000,  reward:'Silver Frame', rewardSP:0  },
  { id:13, cat:'fans',   name:'Gold Class',           desc:'Reach 50,000 fans',                              icon:'🥇', unlocked:false, progress:8200, target:50000,  reward:'Gold Frame',  rewardSP:0  },
  { id:14, cat:'fans',   name:'Platinum Class',       desc:'Reach 100,000 fans — benchmark milestone',       icon:'💠', unlocked:false, progress:8200, target:100000, reward:'Plat Frame',  rewardSP:0  },
  { id:15, cat:'fans',   name:'Legend Class',         desc:'Reach 320,000 fans — maximum rank',              icon:'🌠', unlocked:false, progress:8200, target:320000, reward:'Legend Title',rewardSP:0  },
  // Skills
  { id:16, cat:'skills', name:'First Skill',          desc:'Acquire your first skill',                       icon:'✨', unlocked:true,  unlockedAt:'Turn 12', reward:'30 SP',  rewardSP:30  },
  { id:17, cat:'skills', name:'Skill Collector',      desc:'Acquire 10 skills',                              icon:'💡', unlocked:false, progress:5,    target:10,     reward:'150 SP',      rewardSP:150 },
  { id:18, cat:'skills', name:'Rare Skill',           desc:'Evolve a skill to Gold tier',                    icon:'⭐', unlocked:false, progress:0,    target:1,      reward:'200 SP',      rewardSP:200 },
  // Career
  { id:19, cat:'career', name:'Junior Graduate',      desc:'Complete Junior Year (Turn 24)',                  icon:'🎓', unlocked:true,  unlockedAt:'Turn 24', reward:'120 SP', rewardSP:120 },
  { id:20, cat:'career', name:'Classic Contender',    desc:'Complete Classic Year (Turn 48)',                 icon:'🏇', unlocked:false, progress:45,   target:48,     reward:'250 SP',      rewardSP:250 },
  { id:21, cat:'career', name:'URA Finalist',         desc:'Reach URA Finals (Turn 73)',                     icon:'🏅', unlocked:false, progress:45,   target:73,     reward:'1,000 SP',    rewardSP:1000},
];

const MOCK_SNAPSHOTS = [
  { id:1, label:'Before Mile Cup', turn:44, createdAt:'Apr 7, 2026', trigger:'manual', checksum:'a1b2c3d4', stats:{ speed:472, stamina:438, power:405, guts:420, wit:412 }, sp:380, mood:'Good', energy:82 },
  { id:2, label:'Junior Year End', turn:24, createdAt:'Mar 12, 2026', trigger:'milestone', checksum:'d4e5f6a7', stats:{ speed:280, stamina:250, power:220, guts:240, wit:230 }, sp:120, mood:'Great', energy:90 },
];

// ── Achievements Screen ───────────────────────────────────────────────────────
function Achievements() {
  const [filter, setFilter] = React.useState('all');
  const [toastAch, setToastAch] = React.useState(null);
  const [localUnlocked, setLocalUnlocked] = React.useState(new Set());
  const char = MOCK_CHARACTERS[0];

  const catMeta = {
    all:    { label:'All',         icon:'🎖️' },
    stats:  { label:'Stats',       icon:'📊' },
    races:  { label:'Races',       icon:'🏆' },
    fans:   { label:'Fan Class',   icon:'👥' },
    skills: { label:'Skills',      icon:'✨' },
    career: { label:'Career',      icon:'🏇' },
  };

  const tierColor = { unlocked:'#F59E0B', progress:'#7C3AED', locked:'#D1D5DB' };
  const catColor  = { stats:'#E879A0', races:'#F59E0B', fans:'#10B981', skills:'#7C3AED', career:'#3B82F6' };

  const isUnlocked = a => a.unlocked || localUnlocked.has(a.id);

  const filtered = ACHIEVEMENTS_DATA.filter(a => filter === 'all' || a.cat === filter);
  const totalUnlocked = ACHIEVEMENTS_DATA.filter(isUnlocked).length;
  const totalSPEarned = ACHIEVEMENTS_DATA.filter(isUnlocked).reduce((s,a)=>s+(a.rewardSP||0),0);
  const currentFans   = 8200;

  const handleDemo = () => {
    const locked = ACHIEVEMENTS_DATA.filter(a => !isUnlocked(a));
    if (!locked.length) return;
    const pick = locked[Math.floor(Math.random() * locked.length)];
    setLocalUnlocked(prev => new Set([...prev, pick.id]));
    setToastAch(pick);
    setTimeout(() => setToastAch(null), 4000);
  };

  return (
    <div>
      {/* Header */}
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:24 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Achievements</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{char.name} · {totalUnlocked}/{ACHIEVEMENTS_DATA.length} unlocked</div>
        </div>
        <Btn variant='secondary' size='sm' onClick={handleDemo}>🎲 Demo Unlock</Btn>
      </div>

      {/* Summary cards */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:14, marginBottom:24 }}>
        {[
          { label:'Achievements', val:`${totalUnlocked}/${ACHIEVEMENTS_DATA.length}`, icon:'🎖️', color:'#7C3AED', bg:'#F5F3FF' },
          { label:'SP from Achievements', val:`${totalSPEarned.toLocaleString()}`, icon:'✨', color:'#F59E0B', bg:'#FFFBEB' },
          { label:'Current Fans', val:currentFans.toLocaleString(), icon:'👥', color:'#10B981', bg:'#F0FDF4' },
          { label:'Current Class', val:'Bronze', icon:'🥉', color:'#F97316', bg:'#FFF7ED' },
        ].map(item=>(
          <Card key={item.label} style={{ padding:18, background:item.bg, border:`1px solid ${item.color}22` }}>
            <div style={{ fontSize:11, fontWeight:700, color:item.color, marginBottom:4 }}>{item.icon} {item.label}</div>
            <div style={{ fontSize:26, fontWeight:900, color:item.color }}>{item.val}</div>
          </Card>
        ))}
      </div>

      {/* Fan class progress strip */}
      <Card style={{ padding:18, marginBottom:24, background:'linear-gradient(135deg,#ECFDF5,#D1FAE5)', border:'1px solid #6EE7B7' }}>
        <div style={{ fontSize:13, fontWeight:800, color:'#065F46', marginBottom:10 }}>👥 Fan Class Progression</div>
        <div style={{ display:'flex', gap:0, alignItems:'center', overflowX:'auto' }}>
          {[
            { name:'Debut', fans:0 }, { name:'Bronze', fans:5000 }, { name:'Silver', fans:20000 },
            { name:'Gold', fans:50000 }, { name:'Platinum', fans:100000 }, { name:'Star', fans:160000 },
            { name:'Top Star', fans:240000 }, { name:'Legend', fans:320000 },
          ].map((cls,i,arr)=>{
            const reached = currentFans >= cls.fans;
            const isNext  = !reached && (i===0 || currentFans >= arr[i-1].fans);
            return (
              <React.Fragment key={cls.name}>
                <div style={{ textAlign:'center', flexShrink:0 }}>
                  <div style={{ width:32, height:32, borderRadius:99, background:reached?'linear-gradient(135deg,#F59E0B,#F97316)':isNext?'#EDE9FE':'#E5E7EB', display:'flex', alignItems:'center', justifyContent:'center', fontSize:14, margin:'0 auto 4px', border:isNext?'2px dashed #C4B5FD':'none' }}>
                    {reached?'✓':isNext?'→':'·'}
                  </div>
                  <div style={{ fontSize:9, fontWeight:800, color:reached?'#065F46':isNext?'#7C3AED':'#9CA3AF', whiteSpace:'nowrap' }}>{cls.name}</div>
                  <div style={{ fontSize:8, color:'#7C6FAB' }}>{cls.fans>=1000?`${cls.fans/1000}k`:cls.fans||'—'}</div>
                </div>
                {i < arr.length-1 && <div style={{ flex:1, height:2, background:currentFans>=arr[i+1].fans?'#F59E0B':'#E5E7EB', minWidth:16 }}/>}
              </React.Fragment>
            );
          })}
        </div>
        <div style={{ fontSize:12, color:'#047857', marginTop:10, fontWeight:700 }}>
          Next: <strong>Silver Class</strong> at 20,000 fans · {(20000-currentFans).toLocaleString()} more needed
        </div>
      </Card>

      {/* Category filters */}
      <div style={{ display:'flex', gap:6, marginBottom:20, flexWrap:'wrap' }}>
        {Object.entries(catMeta).map(([id,m])=>{
          const count = id==='all' ? ACHIEVEMENTS_DATA.filter(isUnlocked).length : ACHIEVEMENTS_DATA.filter(a=>a.cat===id&&isUnlocked(a)).length;
          const total = id==='all' ? ACHIEVEMENTS_DATA.length : ACHIEVEMENTS_DATA.filter(a=>a.cat===id).length;
          return (
            <button key={id} onClick={()=>setFilter(id)} style={{ padding:'8px 16px', borderRadius:10, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, background:filter===id?`linear-gradient(135deg,${catColor[id]||'#E879A0'},${id==='all'?'#7C3AED':catColor[id]+'cc'})`:filter===id?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:filter===id?'#fff':'#7C6FAB', display:'flex', gap:6, alignItems:'center' }}>
              {m.icon} {m.label} <span style={{ fontSize:11, opacity:.8 }}>{count}/{total}</span>
            </button>
          );
        })}
      </div>

      {/* Achievements grid */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(280px,1fr))', gap:14 }}>
        {filtered.map(a => {
          const unlocked = isUnlocked(a);
          const pct = a.target ? Math.min(100, Math.round((a.progress||0)/a.target*100)) : 0;
          const cc  = catColor[a.cat] || '#7C3AED';
          return (
            <div key={a.id} style={{
              borderRadius:14, border:`1px solid ${unlocked?cc+'55':'#EDE9FE'}`,
              background: unlocked ? `linear-gradient(135deg,${cc}0a,${cc}18)` : '#fff',
              padding:16, position:'relative', overflow:'hidden',
              opacity: !unlocked && !a.progress ? .7 : 1,
              transition:'all .2s',
            }}>
              {unlocked && <div style={{ position:'absolute', top:0, left:0, right:0, height:3, background:cc }}/>}

              <div style={{ display:'flex', gap:12, alignItems:'flex-start', marginBottom:10 }}>
                <div style={{ width:46, height:46, borderRadius:12, background:unlocked?cc+'22':'#F3F4F6', display:'flex', alignItems:'center', justifyContent:'center', fontSize:22, flexShrink:0, filter:unlocked?'none':'grayscale(1)' }}>{a.icon}</div>
                <div style={{ flex:1 }}>
                  <div style={{ fontSize:14, fontWeight:800, color:unlocked?'#1E1033':'#6B7280', lineHeight:1.3 }}>{a.name}</div>
                  <div style={{ fontSize:11, color:'#7C6FAB', marginTop:3, lineHeight:1.4 }}>{a.desc}</div>
                </div>
                {unlocked && <span style={{ fontSize:18 }}>✅</span>}
              </div>

              {/* Progress bar for in-progress */}
              {!unlocked && a.target && (
                <div style={{ marginBottom:10 }}>
                  <div style={{ display:'flex', justifyContent:'space-between', fontSize:11, color:'#7C6FAB', marginBottom:4 }}>
                    <span>{(a.progress||0).toLocaleString()} / {a.target.toLocaleString()}</span>
                    <span style={{ fontWeight:700 }}>{pct}%</span>
                  </div>
                  <div style={{ height:6, background:'#EDE9FE', borderRadius:99 }}>
                    <div style={{ height:'100%', width:`${pct}%`, background:`linear-gradient(90deg,${cc},${cc}cc)`, borderRadius:99, transition:'width .5s' }}/>
                  </div>
                </div>
              )}

              <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                <div style={{ display:'flex', gap:6, alignItems:'center' }}>
                  <span style={{ fontSize:10, fontWeight:800, background:unlocked?cc+'22':'#F3F4F6', color:unlocked?cc:'#9CA3AF', borderRadius:6, padding:'2px 8px', textTransform:'uppercase', letterSpacing:.5 }}>{a.cat}</span>
                  <span style={{ fontSize:11, color:'#F59E0B', fontWeight:800 }}>🎁 {a.reward}</span>
                </div>
                {a.unlockedAt && <span style={{ fontSize:10, color:'#7C6FAB', fontWeight:600 }}>{a.unlockedAt}</span>}
              </div>
            </div>
          );
        })}
      </div>

      {/* Achievement unlock toast */}
      {toastAch && (
        <div style={{ position:'fixed', bottom:32, right:32, background:'linear-gradient(135deg,#1E1033,#3B1F6E)', borderRadius:20, padding:'18px 24px', zIndex:200, boxShadow:'0 8px 40px rgba(124,58,237,0.5)', display:'flex', gap:14, alignItems:'center', minWidth:320, animation:'slideUp .3s ease' }}>
          <div style={{ width:48, height:48, borderRadius:14, background:'linear-gradient(135deg,#F59E0B,#F97316)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:24, flexShrink:0 }}>{toastAch.icon}</div>
          <div>
            <div style={{ fontSize:11, fontWeight:800, color:'rgba(255,255,255,0.5)', letterSpacing:1, marginBottom:2 }}>🎉 ACHIEVEMENT UNLOCKED</div>
            <div style={{ fontSize:15, fontWeight:900, color:'#fff' }}>{toastAch.name}</div>
            <div style={{ fontSize:12, color:'rgba(255,255,255,0.6)', marginTop:2 }}>Reward: <span style={{ color:'#FCD34D', fontWeight:800 }}>{toastAch.reward}</span></div>
          </div>
        </div>
      )}
    </div>
  );
}

// ── Snapshots component (used inside CharacterDetail) ─────────────────────────
function Snapshots({ char }) {
  const [snapshots, setSnapshots] = React.useState(MOCK_SNAPSHOTS);
  const [creating, setCreating] = React.useState(false);
  const [newLabel, setNewLabel] = React.useState('');
  const [comparing, setComparing] = React.useState(null); // {a, b}
  const [restoring, setRestoring] = React.useState(null);
  const [restored, setRestored] = React.useState(null);

  const handleCreate = () => {
    const snap = {
      id: snapshots.length + 1,
      label: newLabel || `Turn ${char.turn} Checkpoint`,
      turn: char.turn,
      createdAt: 'Apr 21, 2026',
      trigger: 'manual',
      checksum: Math.random().toString(36).slice(2,10),
      stats: { ...char.stats },
      sp: char.sp,
      mood: char.mood,
      energy: char.energy,
    };
    setSnapshots(s => [snap, ...s]);
    setCreating(false);
    setNewLabel('');
  };

  const handleRestore = (snap) => {
    setRestoring(snap);
    setTimeout(() => { setRestoring(null); setRestored(snap.id); setTimeout(()=>setRestored(null),2500); }, 1500);
  };

  const statDiff = (a, b) => {
    return Object.keys(a.stats).map(s => ({
      stat: s, a: a.stats[s], b: b.stats[s], diff: b.stats[s] - a.stats[s]
    }));
  };

  return (
    <div>
      {/* Header */}
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:16 }}>
        <div style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>Run Snapshots</div>
        <Btn variant='primary' size='sm' onClick={()=>setCreating(true)}>+ Create Checkpoint</Btn>
      </div>

      {/* Create form */}
      {creating && (
        <Card style={{ padding:16, marginBottom:16, background:'#F5F3FF', border:'1px solid #C4B5FD' }}>
          <div style={{ fontSize:13, fontWeight:800, color:'#7C3AED', marginBottom:10 }}>New Checkpoint — Turn {char.turn}</div>
          <input
            value={newLabel}
            onChange={e=>setNewLabel(e.target.value)}
            placeholder={`Turn ${char.turn} Checkpoint`}
            style={{ width:'100%', padding:'9px 12px', borderRadius:10, border:'1px solid #C4B5FD', fontFamily:'Nunito,sans-serif', fontSize:13, color:'#1E1033', background:'#fff', outline:'none', marginBottom:10, boxSizing:'border-box' }}
          />
          <div style={{ display:'flex', gap:8 }}>
            <Btn variant='primary' size='sm' onClick={handleCreate}>Save Snapshot</Btn>
            <Btn variant='ghost' size='sm' onClick={()=>setCreating(false)}>Cancel</Btn>
          </div>
        </Card>
      )}

      {/* Snapshot list */}
      <div style={{ display:'flex', flexDirection:'column', gap:10 }}>
        {snapshots.map(snap => (
          <div key={snap.id} style={{ padding:'16px', background:restored===snap.id?'#D1FAE5':'#F9F5FF', borderRadius:14, border:`1px solid ${restored===snap.id?'#6EE7B7':'#EDE9FE'}`, transition:'all .3s' }}>
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:10 }}>
              <div>
                <div style={{ display:'flex', gap:8, alignItems:'center' }}>
                  <span style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>{snap.label}</span>
                  <span style={{ fontSize:10, background:snap.trigger==='milestone'?'#EDE9FE':'#F3F4F6', color:snap.trigger==='milestone'?'#7C3AED':'#6B7280', borderRadius:6, padding:'1px 7px', fontWeight:700, textTransform:'capitalize' }}>{snap.trigger}</span>
                </div>
                <div style={{ fontSize:12, color:'#7C6FAB', marginTop:2 }}>Turn {snap.turn} · {snap.createdAt} · <span style={{ fontFamily:'monospace', fontSize:11 }}>{snap.checksum}</span></div>
              </div>
              <div style={{ display:'flex', gap:6 }}>
                {snapshots.length >= 2 && snap.id !== snapshots[0].id && (
                  <button onClick={()=>setComparing({a:snap, b:snapshots[0]})} style={{ background:'#EDE9FE', border:'none', borderRadius:8, padding:'5px 10px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, color:'#7C3AED' }}>Compare</button>
                )}
                <button onClick={()=>handleRestore(snap)} disabled={!!restoring} style={{ background:restoring?.id===snap.id?'#D1FAE5':'linear-gradient(135deg,#E879A0,#7C3AED)', border:'none', borderRadius:8, padding:'5px 10px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, color:'#fff', opacity:restoring&&restoring.id!==snap.id?.5:1 }}>
                  {restoring?.id===snap.id ? '⏳ Restoring…' : restored===snap.id ? '✓ Restored!' : 'Restore'}
                </button>
              </div>
            </div>

            {/* Mini stats */}
            <div style={{ display:'flex', gap:8, flexWrap:'wrap' }}>
              {Object.entries(snap.stats).map(([s,v])=>(
                <span key={s} style={{ fontSize:11, fontWeight:800, color:STAT_COLORS[s], background:STAT_COLORS[s]+'15', borderRadius:6, padding:'2px 8px' }}>
                  {STAT_ICONS[s]} {v}
                </span>
              ))}
              <span style={{ fontSize:11, fontWeight:800, color:'#F59E0B', background:'#FFFBEB', borderRadius:6, padding:'2px 8px' }}>✨ {snap.sp} SP</span>
              <MoodChip mood={snap.mood} />
            </div>
          </div>
        ))}
      </div>

      {/* Compare modal */}
      {comparing && (
        <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.8)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
          <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:560, overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.3)', maxHeight:'90vh', overflowY:'auto' }}>
            <div style={{ background:'linear-gradient(135deg,#1E1033,#3B1F6E)', padding:'18px 24px', display:'flex', justifyContent:'space-between', alignItems:'center' }}>
              <div style={{ fontSize:16, fontWeight:900, color:'#fff' }}>Snapshot Comparison</div>
              <button onClick={()=>setComparing(null)} style={{ background:'rgba(255,255,255,0.1)', border:'none', borderRadius:8, padding:'6px 10px', cursor:'pointer', color:'#fff', fontSize:16 }}>✕</button>
            </div>
            <div style={{ padding:24 }}>
              <div style={{ display:'grid', gridTemplateColumns:'1fr auto 1fr', gap:12, marginBottom:20, alignItems:'center' }}>
                <div style={{ background:'#F9F5FF', borderRadius:10, padding:'10px 12px', textAlign:'center' }}>
                  <div style={{ fontSize:12, fontWeight:800, color:'#7C6FAB' }}>Snapshot A</div>
                  <div style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>{comparing.a.label}</div>
                  <div style={{ fontSize:11, color:'#7C6FAB' }}>Turn {comparing.a.turn}</div>
                </div>
                <div style={{ fontSize:20 }}>→</div>
                <div style={{ background:'#EDE9FE', borderRadius:10, padding:'10px 12px', textAlign:'center' }}>
                  <div style={{ fontSize:12, fontWeight:800, color:'#7C3AED' }}>Snapshot B (current)</div>
                  <div style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>{comparing.b.label}</div>
                  <div style={{ fontSize:11, color:'#7C6FAB' }}>Turn {comparing.b.turn}</div>
                </div>
              </div>

              <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:10 }}>Stat Changes</div>
              {statDiff(comparing.a, comparing.b).map(d => (
                <div key={d.stat} style={{ display:'flex', alignItems:'center', gap:12, padding:'10px 14px', background:'#F9F5FF', borderRadius:10, marginBottom:6 }}>
                  <span style={{ fontSize:16 }}>{STAT_ICONS[d.stat]}</span>
                  <span style={{ fontSize:13, fontWeight:700, color:'#1E1033', width:70 }}>{STAT_LABELS[d.stat]}</span>
                  <span style={{ fontSize:13, fontWeight:800, color:STAT_COLORS[d.stat] }}>{d.a}</span>
                  <div style={{ flex:1, height:5, background:'#EDE9FE', borderRadius:99, overflow:'hidden' }}>
                    <div style={{ height:'100%', width:`${(d.b/1200)*100}%`, background:STAT_COLORS[d.stat], borderRadius:99 }}/>
                  </div>
                  <span style={{ fontSize:13, fontWeight:800, color:STAT_COLORS[d.stat] }}>{d.b}</span>
                  <span style={{ fontSize:13, fontWeight:900, color:d.diff>0?'#10B981':d.diff<0?'#EF4444':'#7C6FAB', minWidth:44, textAlign:'right' }}>{d.diff>0?`+${d.diff}`:d.diff}</span>
                </div>
              ))}

              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8, marginTop:14 }}>
                {[
                  { label:'SP', a:comparing.a.sp, b:comparing.b.sp, icon:'✨', color:'#F59E0B' },
                  { label:'Turn Delta', a:comparing.a.turn, b:comparing.b.turn, icon:'🔄', color:'#7C3AED' },
                ].map(row=>(
                  <div key={row.label} style={{ padding:'10px 14px', background:'#F9F5FF', borderRadius:10, display:'flex', justifyContent:'space-between' }}>
                    <span style={{ fontSize:12, color:'#7C6FAB' }}>{row.icon} {row.label}</span>
                    <span style={{ fontSize:13, fontWeight:900, color:row.color }}>{row.a} → {row.b} (<span style={{ color:row.b-row.a>=0?'#10B981':'#EF4444' }}>{row.b-row.a>=0?'+':''}{row.b-row.a}</span>)</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

Object.assign(window, { Achievements, Snapshots, ACHIEVEMENTS_DATA, MOCK_SNAPSHOTS });
