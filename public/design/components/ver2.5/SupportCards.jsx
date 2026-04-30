
function SupportCards() {
  const [selected, setSelected] = React.useState(null);
  const [upgrading, setUpgrading] = React.useState(null); // card being limit-broken
  const [upgradeResult, setUpgradeResult] = React.useState(null);
  const [deck, setDeck] = React.useState(SUPPORT_DECK.map(c=>({ ...c, limitBreak:0 })));

  const typeColor = { Speed:'#E879A0', Stamina:'#10B981', Power:'#F59E0B', Guts:'#EF4444', Wit:'#3B82F6', Friend:'#7C3AED' };
  const typeIcon  = { Speed:'⚡', Stamina:'🌿', Power:'🔥', Guts:'❤️', Wit:'💙', Friend:'🤝' };
  const tierBg    = { 'S+':'linear-gradient(135deg,#F59E0B,#EF4444)', S:'linear-gradient(135deg,#F59E0B,#F97316)', 'A+':'linear-gradient(135deg,#E879A0,#7C3AED)', A:'linear-gradient(135deg,#C4B5FD,#7C3AED)', B:'#D1D5DB' };

  const deckSynergy = 78;
  const avgBond = Math.round(deck.reduce((a,c)=>a+c.bond,0)/deck.length);
  const friendshipActive = deck.filter(c=>c.bond>=80);

  // Limit break increments bond cap by 5% per level, max 4 breaks
  const maxBond = (card) => Math.min(100, 80 + (card.limitBreak||0) * 5);

  const handleUpgrade = (card) => {
    setUpgrading(card);
  };

  const confirmUpgrade = (card) => {
    setDeck(d => d.map(c => c.id===card.id ? { ...c, limitBreak:Math.min(4,(c.limitBreak||0)+1), bond:Math.min(c.bond+5, 100) } : c));
    setUpgradeResult(card);
    setUpgrading(null);
    setSelected(null);
    setTimeout(()=>setUpgradeResult(null), 3000);
  };

  // ── Upgrade Modal ────────────────────────────────────────────────────────
  const UpgradeModal = () => {
    if (!upgrading) return null;
    const card = deck.find(c=>c.id===upgrading.id) || upgrading;
    const currentLB = card.limitBreak || 0;
    const tc = typeColor[card.type] || '#7C3AED';
    if (currentLB >= 4) return null;
    return (
      <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.8)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
        <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:440, overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.3)' }}>
          <div style={{ background:`linear-gradient(135deg,${tc}44,${tc}88)`, padding:'20px 24px' }}>
            <div style={{ fontSize:32, marginBottom:6 }}>{typeIcon[card.type]}</div>
            <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>Limit Break — {card.name}</div>
            <div style={{ fontSize:13, color:'rgba(0,0,0,0.5)', marginTop:2 }}>Level {currentLB} → {currentLB+1} of 4</div>
          </div>
          <div style={{ padding:24 }}>
            {/* LB dots */}
            <div style={{ display:'flex', gap:8, justifyContent:'center', marginBottom:20 }}>
              {[0,1,2,3].map(i=>(
                <div key={i} style={{ width:36, height:36, borderRadius:99, background: i<=currentLB?tc:'#EDE9FE', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16, transition:'background .2s' }}>
                  {i<currentLB?'✓':i===currentLB?'→':'·'}
                </div>
              ))}
            </div>

            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:10, marginBottom:16 }}>
              {[
                { label:'Bond Cap', before:`${maxBond(card)}%`, after:`${Math.min(100,maxBond(card)+5)}%`, color:tc },
                { label:'Limit Break', before:`Lv.${currentLB}`, after:`Lv.${currentLB+1}`, color:'#7C3AED' },
                { label:'Stat Bonus', before:'Base', after:'+5% all', color:'#10B981' },
                { label:'Friendship Mult', before:'1.10×', after:currentLB>=2?'1.35×':'1.10×', color:'#E879A0' },
              ].map(item=>(
                <div key={item.label} style={{ background:'#F9F5FF', borderRadius:10, padding:'10px 12px', border:'1px solid #EDE9FE' }}>
                  <div style={{ fontSize:11, color:'#7C6FAB', fontWeight:700, marginBottom:4 }}>{item.label}</div>
                  <div style={{ display:'flex', gap:6, alignItems:'center' }}>
                    <span style={{ fontSize:12, color:'#7C6FAB', textDecoration:'line-through' }}>{item.before}</span>
                    <span style={{ fontSize:14, fontWeight:900, color:item.color }}>{item.after}</span>
                  </div>
                </div>
              ))}
            </div>

            <div style={{ background:'#FEF3C7', borderRadius:10, padding:'10px 14px', marginBottom:16, fontSize:12, color:'#92400E', fontWeight:700 }}>
              ⚠️ Validation: max 1 Friend card · no duplicate owned cards · limit_break_level 0–4
            </div>

            <div style={{ display:'flex', gap:10 }}>
              <Btn variant='gold' style={{ flex:1, justifyContent:'center' }} onClick={()=>confirmUpgrade(card)}>⬆️ Confirm Limit Break</Btn>
              <Btn variant='ghost' onClick={()=>setUpgrading(null)}>Cancel</Btn>
            </div>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>Support Cards & Deck</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>6-card deck · Bond tracking · Limit break · Friendship Training</div>

      {/* Upgrade result toast */}
      {upgradeResult && (
        <div style={{ position:'fixed', bottom:32, right:32, background:'linear-gradient(135deg,#78350F,#92400E)', borderRadius:16, padding:'14px 22px', zIndex:200, boxShadow:'0 8px 32px rgba(245,158,11,0.4)', display:'flex', gap:12, alignItems:'center' }}>
          <span style={{ fontSize:24 }}>⬆️</span>
          <div>
            <div style={{ fontSize:12, fontWeight:800, color:'#FEF3C7' }}>LIMIT BREAK!</div>
            <div style={{ fontSize:14, fontWeight:900, color:'#fff' }}>{upgradeResult.name} upgraded</div>
          </div>
        </div>
      )}

      {/* Overview stats */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:14, marginBottom:24 }}>
        {[
          { label:'Deck Synergy', value:`${deckSynergy}%`, icon:'🎯', color:'#7C3AED', bg:'#F5F3FF', border:'#EDE9FE' },
          { label:'Avg Bond', value:`${avgBond}%`, icon:'💗', color:'#E879A0', bg:'#FDF2F8', border:'#F9A8D4' },
          { label:'Friendship Active', value:`${friendshipActive.length} cards`, icon:'🤝', color:'#10B981', bg:'#F0FDF4', border:'#BBF7D0' },
          { label:'SSR Cards', value:deck.filter(c=>c.rarity==='SSR').length, icon:'⭐', color:'#F59E0B', bg:'#FFFBEB', border:'#FCD34D' },
        ].map(item=>(
          <Card key={item.label} style={{ padding:18, background:item.bg, border:`1px solid ${item.border}` }}>
            <div style={{ fontSize:11, fontWeight:700, color:item.color, marginBottom:4 }}>{item.icon} {item.label}</div>
            <div style={{ fontSize:28, fontWeight:900, color:item.color }}>{item.value}</div>
          </Card>
        ))}
      </div>

      {/* Friendship training alert */}
      {friendshipActive.length > 0 && (
        <div style={{ background:'linear-gradient(135deg,#ECFDF5,#D1FAE5)', borderRadius:14, padding:'12px 18px', marginBottom:20, border:'1px solid #6EE7B7', display:'flex', gap:12, alignItems:'center' }}>
          <span style={{ fontSize:24 }}>🤝</span>
          <div style={{ flex:1 }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#065F46' }}>Friendship Training Active — {friendshipActive.length >= 3 ? '1.35×' : '1.10×'} multiplier</div>
            <div style={{ fontSize:12, color:'#047857' }}>{friendshipActive.map(c=>c.name).join(', ')} at ≥80% bond · Boosts all training gains this turn</div>
          </div>
        </div>
      )}

      <div style={{ display:'grid', gridTemplateColumns:selected?'1fr 320px':'1fr', gap:20 }}>
        {/* Deck grid */}
        <div>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Active Deck</div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:14, marginBottom:24 }}>
            {deck.map(card => {
              const tc  = typeColor[card.type] || '#7C3AED';
              const ti  = typeIcon[card.type] || '🃏';
              const sel = selected?.id===card.id;
              const lb  = card.limitBreak || 0;
              const ftActive = card.bond >= 80;
              return (
                <div key={card.id} onClick={()=>setSelected(sel?null:card)} style={{ borderRadius:16, overflow:'hidden', cursor:'pointer', border:`2px solid ${sel?tc:'transparent'}`, boxShadow:sel?`0 4px 20px ${tc}44`:'0 2px 8px rgba(124,58,237,0.07)', transition:'all .15s' }}>
                  <div style={{ background:`linear-gradient(135deg,${tc}22,${tc}44)`, padding:'14px 14px 10px', borderBottom:`1px solid ${tc}22`, position:'relative' }}>
                    <div style={{ position:'absolute', top:8, right:8, display:'flex', gap:4 }}>
                      <span style={{ background:tierBg[card.tier]||'#EDE9FE', color:'#fff', borderRadius:6, padding:'2px 7px', fontSize:10, fontWeight:800 }}>{card.tier}</span>
                      {lb>0 && <span style={{ background:'linear-gradient(135deg,#F59E0B,#F97316)', color:'#fff', borderRadius:6, padding:'2px 7px', fontSize:10, fontWeight:800 }}>LB{lb}</span>}
                    </div>
                    <div style={{ fontSize:24, marginBottom:4 }}>{ti}</div>
                    <div style={{ fontSize:10, fontWeight:800, color:tc, textTransform:'uppercase', letterSpacing:.5 }}>{card.type}</div>
                    <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', lineHeight:1.3 }}>{card.name}</div>
                    <RarityBadge rarity={card.rarity} />
                  </div>
                  <div style={{ padding:'12px 14px', background:'#fff' }}>
                    <div style={{ display:'flex', justifyContent:'space-between', fontSize:11, fontWeight:700, marginBottom:4 }}>
                      <span style={{ color:'#7C6FAB' }}>Bond{ftActive?<span style={{ color:'#10B981' }}> 🤝</span>:''}</span>
                      <span style={{ color:tc, fontWeight:900 }}>{card.bond}% <span style={{ color:'#9CA3AF', fontWeight:500 }}>/ {Math.min(100,80+(lb*5))}%</span></span>
                    </div>
                    <div style={{ height:6, background:'#EDE9FE', borderRadius:99, position:'relative' }}>
                      <div style={{ height:'100%', width:`${card.bond}%`, background: ftActive?'#10B981':tc, borderRadius:99 }}/>
                      <div style={{ position:'absolute', top:-1, left:`${Math.min(100,80+lb*5)}%`, height:'8px', width:2, background:'rgba(0,0,0,0.2)' }}/>
                    </div>
                    {lb < 4 && (
                      <button onClick={e=>{e.stopPropagation();handleUpgrade(card);}} style={{ marginTop:8, width:'100%', padding:'5px', borderRadius:8, border:`1px solid ${tc}44`, background:tc+'11', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:800, color:tc }}>
                        ⬆️ Limit Break (Lv.{lb}→{lb+1})
                      </button>
                    )}
                    {lb >= 4 && <div style={{ marginTop:8, textAlign:'center', fontSize:10, fontWeight:800, color:'#F59E0B' }}>⭐ Max Limit Break</div>}
                  </div>
                </div>
              );
            })}
          </div>

          {/* Validation rules */}
          <Card style={{ padding:16, background:'#F9F5FF' }}>
            <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:10 }}>Deck Validation Rules</div>
            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8 }}>
              {[
                { rule:'Exactly 6 cards required', ok:deck.length===6, icon:'🃏' },
                { rule:'Max 1 Friend card', ok:deck.filter(c=>c.type==='Friend').length<=1, icon:'🤝' },
                { rule:'No duplicate owned cards', ok:true, icon:'🚫' },
                { rule:'Limit break 0–4', ok:deck.every(c=>(c.limitBreak||0)<=4), icon:'⬆️' },
              ].map(v=>(
                <div key={v.rule} style={{ display:'flex', gap:8, alignItems:'center', padding:'8px 10px', background:'#fff', borderRadius:8, border:'1px solid #EDE9FE' }}>
                  <span style={{ fontSize:14 }}>{v.icon}</span>
                  <span style={{ flex:1, fontSize:12, fontWeight:600, color:'#1E1033' }}>{v.rule}</span>
                  <span style={{ fontSize:14 }}>{v.ok?'✅':'❌'}</span>
                </div>
              ))}
            </div>
          </Card>
        </div>

        {/* Card detail */}
        {selected && (() => {
          const card = deck.find(c=>c.id===selected.id) || selected;
          const tc = typeColor[card.type] || '#7C3AED';
          const lb = card.limitBreak || 0;
          const ftActive = card.bond >= 80;
          return (
            <Card style={{ padding:22, alignSelf:'start', position:'sticky', top:0 }}>
              <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:16 }}>
                <div>
                  <div style={{ fontSize:17, fontWeight:900, color:'#1E1033' }}>{card.name}</div>
                  <div style={{ display:'flex', gap:6, marginTop:4 }}>
                    <RarityBadge rarity={card.rarity} />
                    <span style={{ fontSize:12, color:'#7C6FAB' }}>{card.type}</span>
                  </div>
                </div>
                <button onClick={()=>setSelected(null)} style={{ background:'#F9F5FF', border:'none', borderRadius:8, padding:6, cursor:'pointer' }}>
                  <Icon name='x' size={16} color='#7C6FAB'/>
                </button>
              </div>

              {/* Limit break */}
              <div style={{ marginBottom:16 }}>
                <div style={{ fontSize:12, fontWeight:800, color:'#7C6FAB', marginBottom:8 }}>LIMIT BREAK</div>
                <div style={{ display:'flex', gap:6, marginBottom:6 }}>
                  {[0,1,2,3].map(i=>(
                    <div key={i} style={{ flex:1, height:8, borderRadius:99, background:i<lb?'linear-gradient(90deg,#F59E0B,#F97316)':'#EDE9FE' }}/>
                  ))}
                </div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>Level {lb}/4 · Bond cap: {Math.min(100,80+lb*5)}%</div>
              </div>

              {/* Bond */}
              <div style={{ marginBottom:16 }}>
                <div style={{ display:'flex', justifyContent:'space-between', fontSize:13, fontWeight:700, color:'#1E1033', marginBottom:6 }}>
                  <span>Bond Progress {ftActive&&<span style={{color:'#10B981'}}>🤝</span>}</span>
                  <span style={{ color:tc, fontWeight:900 }}>{card.bond}%</span>
                </div>
                <div style={{ height:10, background:'#EDE9FE', borderRadius:99, overflow:'hidden', position:'relative' }}>
                  <div style={{ height:'100%', width:`${card.bond}%`, background:ftActive?'#10B981':tc, borderRadius:99 }}/>
                  <div style={{ position:'absolute', top:0, left:`${Math.min(100,80+lb*5)}%`, width:2, height:'100%', background:'rgba(0,0,0,0.2)' }}/>
                </div>
                {ftActive && <div style={{ marginTop:6, fontSize:12, fontWeight:700, color:'#10B981' }}>✅ Friendship Training active at ≥80% bond</div>}
              </div>

              {/* Skills */}
              <div style={{ marginBottom:16 }}>
                <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:8 }}>Skills Provided</div>
                {card.skills.map((sk,i)=>(
                  <div key={i} style={{ display:'flex', gap:8, padding:'8px 10px', background:'#F9F5FF', borderRadius:8, marginBottom:6 }}>
                    <span>✨</span><span style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>{sk}</span>
                  </div>
                ))}
              </div>

              {lb < 4 ? (
                <Btn variant='gold' style={{ width:'100%', justifyContent:'center' }} onClick={()=>handleUpgrade(card)}>⬆️ Limit Break (Lv.{lb}→{lb+1})</Btn>
              ) : (
                <div style={{ textAlign:'center', padding:'10px', background:'#FFFBEB', borderRadius:10, fontSize:13, fontWeight:800, color:'#92400E' }}>⭐ Maximum Limit Break</div>
              )}
            </Card>
          );
        })()}
      </div>

      <UpgradeModal />
    </div>
  );
}

Object.assign(window, { SupportCards });
