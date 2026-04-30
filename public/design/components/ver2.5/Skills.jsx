
// Hint level → discount %
const HINT_DISCOUNTS = [0, 10, 20, 30, 35, 40];

// Skills with hint levels and evolution data
const SKILLS_EXTENDED = SKILLS_DATA.map(sk => ({
  ...sk,
  hintLevel: sk.hint ? 2 : sk.owned ? 0 : 0,
  evolution: sk.owned && (sk.id === 2 || sk.id === 3 || sk.id === 4) ? {
    name: sk.name.replace(' I',''+' (Gold)').replace('+','++'),
    cost: Math.round(sk.cost * 0.6),
    requires: sk.id === 2 ? 'Hint Level 3+' : sk.id === 3 ? 'Hint Level 2+' : 'Hint Level 4+',
    unlocked: sk.id === 3,
  } : null,
}));

function Skills() {
  const char = MOCK_CHARACTERS[0];
  const [filter, setFilter] = React.useState('all');
  const [search, setSearch] = React.useState('');
  const [evolving, setEvolving] = React.useState(null);
  const [evolved, setEvolvedSet] = React.useState(new Set());
  const [purchasing, setPurchasing] = React.useState(null);

  const typeColor = { unique:'#F59E0B', speed:'#E879A0', stamina:'#10B981', power:'#F97316', guts:'#EF4444', wit:'#3B82F6', style:'#7C3AED', distance:'#06B6D4', recovery:'#8B5CF6' };
  const typeIcon  = { unique:'⭐', speed:'⚡', stamina:'🌿', power:'🔥', guts:'❤️', wit:'💙', style:'🎯', distance:'🏁', recovery:'💊' };

  const discountedCost = (sk) => {
    const disc = HINT_DISCOUNTS[sk.hintLevel || 0] || 0;
    return Math.round(sk.cost * (1 - disc/100));
  };

  const filtered = SKILLS_EXTENDED.filter(sk => {
    if (filter==='owned')     return sk.owned;
    if (filter==='available') return !sk.owned;
    if (filter==='hints')     return sk.hint || sk.hintLevel > 0;
    if (filter==='evolve')    return sk.evolution;
    return true;
  }).filter(sk => sk.name.toLowerCase().includes(search.toLowerCase()));

  const owned = SKILLS_EXTENDED.filter(s=>s.owned);
  const spUsed = owned.reduce((a,s)=>a+s.cost,0);

  const handlePurchase = (sk) => {
    setPurchasing(sk);
    setTimeout(()=>setPurchasing(null), 2000);
  };

  const handleEvolve = (sk) => {
    setEvolvedSet(prev => new Set([...prev, sk.id]));
    setEvolving(null);
  };

  // ── Evolution Modal ─────────────────────────────────────────────────────
  const EvolutionModal = () => {
    if (!evolving) return null;
    const evo = evolving.evolution;
    const cost = discountedCost({ cost: evo.cost, hintLevel: evolving.hintLevel });
    const canAfford = char.sp >= cost;
    return (
      <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.8)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
        <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:420, overflow:'hidden', boxShadow:'0 24px 80px rgba(245,158,11,0.3)' }}>
          <div style={{ background:'linear-gradient(135deg,#78350F,#92400E)', padding:'20px 24px' }}>
            <div style={{ fontSize:32, marginBottom:6 }}>⬆️</div>
            <div style={{ fontSize:18, fontWeight:900, color:'#FEF3C7' }}>Skill Evolution</div>
            <div style={{ fontSize:13, color:'rgba(255,255,255,0.6)' }}>Upgrade to Gold tier</div>
          </div>
          <div style={{ padding:24 }}>
            <div style={{ display:'flex', alignItems:'center', gap:14, marginBottom:20 }}>
              <div style={{ textAlign:'center', padding:'12px 20px', background:'#F9F5FF', borderRadius:12, border:'1px solid #EDE9FE' }}>
                <div style={{ fontSize:13, fontWeight:800, color:'#7C6FAB' }}>{typeIcon[evolving.type]} {evolving.name}</div>
                <div style={{ fontSize:11, color:'#7C6FAB', marginTop:2 }}>Normal</div>
              </div>
              <div style={{ fontSize:20 }}>→</div>
              <div style={{ textAlign:'center', padding:'12px 20px', background:'linear-gradient(135deg,#FFFBEB,#FEF3C7)', borderRadius:12, border:'2px solid #F59E0B' }}>
                <div style={{ fontSize:13, fontWeight:800, color:'#92400E' }}>⭐ {evo.name}</div>
                <div style={{ fontSize:11, color:'#92400E', marginTop:2, fontWeight:700 }}>Gold</div>
              </div>
            </div>

            <div style={{ background:'#F9F5FF', borderRadius:12, padding:'14px', marginBottom:16 }}>
              <div style={{ display:'flex', justifyContent:'space-between', marginBottom:6 }}>
                <span style={{ fontSize:13, color:'#7C6FAB' }}>Evolution Cost</span>
                <span style={{ fontSize:15, fontWeight:900, color:'#F59E0B' }}>✨ {evo.cost} SP</span>
              </div>
              <div style={{ display:'flex', justifyContent:'space-between', marginBottom:6 }}>
                <span style={{ fontSize:13, color:'#7C6FAB' }}>Your SP</span>
                <span style={{ fontSize:13, fontWeight:800, color: canAfford?'#10B981':'#EF4444' }}>{char.sp} SP</span>
              </div>
              <div style={{ display:'flex', justifyContent:'space-between' }}>
                <span style={{ fontSize:13, color:'#7C6FAB' }}>Requirement</span>
                <span style={{ fontSize:12, fontWeight:800, color:'#10B981' }}>✅ {evo.requires}</span>
              </div>
            </div>

            <div style={{ display:'flex', gap:10 }}>
              <Btn variant='gold' style={{ flex:1, justifyContent:'center' }} onClick={()=>handleEvolve(evolving)} disabled={!canAfford}>
                ⬆️ Evolve Skill
              </Btn>
              <Btn variant='ghost' onClick={()=>setEvolving(null)}>Cancel</Btn>
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:24 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Skill Management</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>{char.name} · {owned.length} skills owned</div>
        </div>
      </div>

      {/* SP budget */}
      <Card style={{ padding:20, marginBottom:20, background:'linear-gradient(135deg,#FFFBEB,#FEF3C7)', border:'1px solid #FCD34D' }}>
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:10 }}>
          <div>
            <div style={{ fontSize:15, fontWeight:800, color:'#92400E' }}>✨ SP Budget</div>
            <div style={{ fontSize:12, color:'#92400E', opacity:.75 }}>Hint discounts reduce purchase costs up to 40%</div>
          </div>
          <div style={{ textAlign:'right' }}>
            <div style={{ fontSize:32, fontWeight:900, color:'#F59E0B' }}>{char.sp}</div>
            <div style={{ fontSize:12, color:'#92400E' }}>of {char.totalSp} total earned</div>
          </div>
        </div>
        <div style={{ height:10, background:'rgba(245,158,11,0.2)', borderRadius:99 }}>
          <div style={{ height:'100%', width:`${(char.usedSp/char.totalSp)*100}%`, background:'linear-gradient(90deg,#F59E0B,#F97316)', borderRadius:99 }}/>
        </div>
        <div style={{ display:'flex', justifyContent:'space-between', fontSize:11, color:'#92400E', marginTop:4 }}>
          <span>Used: {char.usedSp} SP</span><span>Remaining: {char.sp} SP</span>
        </div>
      </Card>

      {/* Hint discount legend */}
      <Card style={{ padding:14, marginBottom:20, background:'#F5F3FF', border:'1px solid #C4B5FD' }}>
        <div style={{ fontSize:12, fontWeight:800, color:'#7C3AED', marginBottom:8 }}>💡 HINT LEVEL DISCOUNTS</div>
        <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
          {HINT_DISCOUNTS.map((d,i)=>(
            <div key={i} style={{ textAlign:'center', background:'#fff', borderRadius:8, padding:'5px 10px', border:'1px solid #EDE9FE' }}>
              <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>Lv.{i}</div>
              <div style={{ fontSize:13, fontWeight:900, color:d>0?'#10B981':'#7C6FAB' }}>{d>0?`-${d}%`:'Base'}</div>
            </div>
          ))}
        </div>
      </Card>

      {/* Filters */}
      <div style={{ display:'flex', gap:8, marginBottom:20, flexWrap:'wrap', alignItems:'center' }}>
        <div style={{ position:'relative', flex:1, minWidth:200 }}>
          <Icon name='search' size={15} color='#7C6FAB' style={{ position:'absolute', left:10, top:'50%', transform:'translateY(-50%)' }}/>
          <input value={search} onChange={e=>setSearch(e.target.value)} placeholder="Search skills..." style={{ width:'100%', padding:'9px 12px 9px 34px', borderRadius:10, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:13, color:'#1E1033', background:'#fff', outline:'none', boxSizing:'border-box' }}/>
        </div>
        {[
          { id:'all',       label:'All Skills' },
          { id:'owned',     label:`Owned (${owned.length})` },
          { id:'available', label:'Available' },
          { id:'hints',     label:'💡 Hints' },
          { id:'evolve',    label:'⬆️ Evolve' },
        ].map(f=>(
          <button key={f.id} onClick={()=>setFilter(f.id)} style={{ padding:'8px 14px', borderRadius:10, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, background:filter===f.id?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:filter===f.id?'#fff':'#7C6FAB' }}>{f.label}</button>
        ))}
      </div>

      {/* Skills grid */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(280px,1fr))', gap:14 }}>
        {filtered.map(sk => {
          const tc = typeColor[sk.type] || '#7C3AED';
          const ti = typeIcon[sk.type] || '✨';
          const disc = HINT_DISCOUNTS[sk.hintLevel || 0] || 0;
          const finalCost = discountedCost(sk);
          const canAfford = !sk.owned && char.sp >= finalCost;
          const isEvolved = evolved.has(sk.id);
          return (
            <div key={sk.id} style={{
              borderRadius:14, border:`1px solid ${sk.owned||isEvolved?tc+'44':'#EDE9FE'}`,
              background: isEvolved ? 'linear-gradient(135deg,#FFFBEB,#FEF3C7)' : sk.owned ? '#fff' : '#F9F5FF',
              padding:16, position:'relative', overflow:'hidden',
              opacity: !sk.owned && !canAfford ? .65 : 1,
            }}>
              {(sk.owned||isEvolved) && <div style={{ position:'absolute', top:0, left:0, right:0, height:3, background: isEvolved?'linear-gradient(90deg,#F59E0B,#F97316)':tc }}/>}
              {isEvolved && <div style={{ position:'absolute', top:10, right:10, background:'linear-gradient(135deg,#F59E0B,#F97316)', color:'#fff', fontSize:9, fontWeight:900, borderRadius:20, padding:'2px 8px' }}>⭐ GOLD</div>}
              {sk.hint && !isEvolved && <div style={{ position:'absolute', top:10, right:10, background:'#D1FAE5', color:'#065F46', fontSize:9, fontWeight:900, borderRadius:20, padding:'2px 8px' }}>💡 HINT</div>}

              <div style={{ display:'flex', gap:10, alignItems:'flex-start', marginBottom:10 }}>
                <div style={{ width:42, height:42, borderRadius:12, background:tc+'18', display:'flex', alignItems:'center', justifyContent:'center', fontSize:20, flexShrink:0 }}>{ti}</div>
                <div style={{ flex:1 }}>
                  <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', lineHeight:1.3 }}>{isEvolved ? sk.evolution?.name : sk.name}</div>
                  <div style={{ display:'flex', gap:5, marginTop:4, flexWrap:'wrap' }}>
                    <span style={{ background:tc+'18', color:tc, borderRadius:6, padding:'2px 8px', fontSize:10, fontWeight:800, textTransform:'capitalize' }}>{sk.type}</span>
                    <span style={{ background: sk.rarity==='unique'?'#FEF3C7':isEvolved?'#FEF3C7':sk.rarity==='rare'?'#EDE9FE':'#F3F4F6', color:sk.rarity==='unique'||isEvolved?'#92400E':sk.rarity==='rare'?'#7C3AED':'#6B7280', borderRadius:6, padding:'2px 8px', fontSize:10, fontWeight:700 }}>{isEvolved?'Gold':sk.rarity}</span>
                  </div>
                </div>
              </div>

              <div style={{ fontSize:12, color:'#7C6FAB', marginBottom:10, lineHeight:1.5 }}>{sk.description}</div>

              {/* Hint level display */}
              {(sk.hintLevel > 0 || sk.owned) && (
                <div style={{ display:'flex', alignItems:'center', gap:8, marginBottom:10, padding:'6px 10px', background:sk.hintLevel>0?'#F0FDF4':'#F9F5FF', borderRadius:8 }}>
                  <span style={{ fontSize:11, color:'#7C6FAB', fontWeight:700 }}>Hint Lv.</span>
                  <div style={{ display:'flex', gap:3 }}>
                    {[0,1,2,3,4].map(i=>(
                      <div key={i} style={{ width:14, height:14, borderRadius:3, background:i<(sk.hintLevel||0)?'#10B981':'#EDE9FE' }}/>
                    ))}
                  </div>
                  {disc > 0 && <span style={{ fontSize:11, fontWeight:800, color:'#10B981', marginLeft:'auto' }}>-{disc}% → {finalCost} SP</span>}
                </div>
              )}

              {sk.hint && sk.hintFrom && (
                <div style={{ background:'#F0FDF4', borderRadius:8, padding:'5px 10px', fontSize:11, color:'#065F46', fontWeight:700, marginBottom:10 }}>From: {sk.hintFrom.join(', ')}</div>
              )}

              <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', gap:8 }}>
                <div>
                  {disc > 0 ? (
                    <div>
                      <span style={{ fontSize:12, color:'#7C6FAB', textDecoration:'line-through' }}>✨ {sk.cost}</span>
                      <span style={{ fontSize:14, fontWeight:900, color:'#10B981', marginLeft:6 }}>✨ {finalCost} SP</span>
                    </div>
                  ) : (
                    <span style={{ fontSize:14, fontWeight:900, color:'#F59E0B' }}>✨ {finalCost} SP</span>
                  )}
                </div>
                <div style={{ display:'flex', gap:6 }}>
                  {sk.owned && sk.evolution && !isEvolved && (
                    <Btn variant={sk.evolution.unlocked?'gold':'ghost'} size='sm' onClick={()=>sk.evolution.unlocked&&setEvolving(sk)} disabled={!sk.evolution.unlocked} style={{ fontSize:11 }}>
                      {sk.evolution.unlocked ? '⬆️ Evolve' : `🔒 ${sk.evolution.requires}`}
                    </Btn>
                  )}
                  {isEvolved && <span style={{ background:'linear-gradient(135deg,#FEF3C7,#FFFBEB)', color:'#92400E', borderRadius:8, padding:'4px 10px', fontSize:12, fontWeight:800, border:'1px solid #FCD34D' }}>⭐ Evolved</span>}
                  {sk.owned && !isEvolved && !sk.evolution && <span style={{ background:'#D1FAE5', color:'#065F46', borderRadius:8, padding:'4px 10px', fontSize:12, fontWeight:800 }}>✓ Owned</span>}
                  {!sk.owned && (
                    <Btn variant={purchasing?.id===sk.id?'secondary':canAfford?'primary':'ghost'} size='sm' disabled={!canAfford} onClick={()=>canAfford&&handlePurchase(sk)}>
                      {purchasing?.id===sk.id ? '✓ Purchased!' : canAfford ? 'Purchase' : `Need ${finalCost-char.sp} more SP`}
                    </Btn>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {filtered.length===0 && (
        <div style={{ textAlign:'center', padding:'60px 20px', color:'#7C6FAB' }}>
          <div style={{ fontSize:40, marginBottom:12 }}>🔍</div>
          <div style={{ fontSize:16, fontWeight:700 }}>No skills found</div>
        </div>
      )}

      <EvolutionModal />
    </div>
  );
}

Object.assign(window, { Skills });
