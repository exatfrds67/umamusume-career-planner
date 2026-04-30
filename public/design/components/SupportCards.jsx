
function SupportCards() {
  const [selected, setSelected] = React.useState(null);
  const typeColor = { Speed:'#E879A0', Stamina:'#10B981', Power:'#F59E0B', Guts:'#EF4444', Wit:'#3B82F6', Friend:'#7C3AED' };
  const typeIcon  = { Speed:'⚡', Stamina:'🌿', Power:'🔥', Guts:'❤️', Wit:'💙', Friend:'🤝' };
  const tierBg    = { 'S+':'linear-gradient(135deg,#F59E0B,#EF4444)', S:'linear-gradient(135deg,#F59E0B,#F97316)', 'A+':'linear-gradient(135deg,#E879A0,#7C3AED)', A:'linear-gradient(135deg,#C4B5FD,#7C3AED)', B:'#D1D5DB' };

  const deckSynergy = 78;
  const avgBond = Math.round(SUPPORT_DECK.reduce((a,c)=>a+c.bond,0)/SUPPORT_DECK.length);

  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>Support Cards & Deck</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>6-card support deck · Synergy analysis · Bond tracking</div>

      {/* Deck overview stats */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:14, marginBottom:24 }}>
        {[
          { label:'Deck Synergy', value:`${deckSynergy}%`, icon:'🎯', color:'#7C3AED', bg:'#F5F3FF', border:'#EDE9FE' },
          { label:'Avg Bond', value:`${avgBond}%`, icon:'💗', color:'#E879A0', bg:'#FDF2F8', border:'#F9A8D4' },
          { label:'SSR Cards', value:SUPPORT_DECK.filter(c=>c.rarity==='SSR').length, icon:'⭐', color:'#F59E0B', bg:'#FFFBEB', border:'#FCD34D' },
          { label:'Cards Filled', value:`${SUPPORT_DECK.filter(Boolean).length}/6`, icon:'🃏', color:'#10B981', bg:'#F0FDF4', border:'#BBF7D0' },
        ].map(item=>(
          <Card key={item.label} style={{ padding:18, background:item.bg, border:`1px solid ${item.border}` }}>
            <div style={{ fontSize:11, fontWeight:700, color:item.color, marginBottom:4 }}>{item.icon} {item.label}</div>
            <div style={{ fontSize:28, fontWeight:900, color:item.color }}>{item.value}</div>
          </Card>
        ))}
      </div>

      <div style={{ display:'grid', gridTemplateColumns: selected ? '1fr 320px' : '1fr', gap:20 }}>
        {/* Deck grid */}
        <div>
          <div style={{ fontSize:15, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Active Deck</div>
          <div style={{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:14, marginBottom:24 }}>
            {SUPPORT_DECK.map(card => {
              const tc = typeColor[card.type] || '#7C3AED';
              const ti = typeIcon[card.type] || '🃏';
              const sel = selected?.id===card.id;
              return (
                <div key={card.id} onClick={()=>setSelected(sel?null:card)} style={{
                  borderRadius:16, overflow:'hidden', cursor:'pointer', border:`2px solid ${sel?tc:'transparent'}`,
                  boxShadow:sel?`0 4px 20px ${tc}44`:'0 2px 8px rgba(124,58,237,0.07)', transition:'all .15s',
                }}>
                  {/* Card header */}
                  <div style={{ background:`linear-gradient(135deg,${tc}22,${tc}44)`, padding:'14px 14px 10px', borderBottom:`1px solid ${tc}22`, position:'relative' }}>
                    <div style={{ position:'absolute', top:10, right:10 }}>
                      <span style={{ background:tierBg[card.tier]||'#EDE9FE', color:'#fff', borderRadius:6, padding:'2px 8px', fontSize:11, fontWeight:800 }}>{card.tier}</span>
                    </div>
                    <div style={{ fontSize:28, marginBottom:4 }}>{ti}</div>
                    <div style={{ fontSize:10, fontWeight:800, color:tc, textTransform:'uppercase', letterSpacing:.5 }}>{card.type}</div>
                    <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', lineHeight:1.3 }}>{card.name}</div>
                    <RarityBadge rarity={card.rarity} />
                  </div>
                  {/* Bond bar */}
                  <div style={{ padding:'12px 14px', background:'#fff' }}>
                    <div style={{ display:'flex', justifyContent:'space-between', fontSize:11, fontWeight:700, marginBottom:4 }}>
                      <span style={{ color:'#7C6FAB' }}>Bond</span>
                      <span style={{ color:tc, fontWeight:900 }}>{card.bond}%</span>
                    </div>
                    <div style={{ height:6, background:'#EDE9FE', borderRadius:99 }}>
                      <div style={{ height:'100%', width:`${card.bond}%`, background:tc, borderRadius:99 }}/>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Type distribution */}
          <Card style={{ padding:20 }}>
            <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Type Distribution</div>
            <div style={{ display:'flex', gap:12, flexWrap:'wrap' }}>
              {Object.entries(typeColor).map(([type, color])=>{
                const count = SUPPORT_DECK.filter(c=>c.type===type).length;
                if (!count) return null;
                return (
                  <div key={type} style={{ display:'flex', alignItems:'center', gap:6, background:'#F9F5FF', borderRadius:10, padding:'8px 14px' }}>
                    <span style={{ fontSize:16 }}>{typeIcon[type]}</span>
                    <span style={{ fontSize:13, fontWeight:800, color }}>{type}</span>
                    <span style={{ fontSize:14, fontWeight:900, color:'#1E1033' }}>×{count}</span>
                  </div>
                );
              })}
            </div>
            <div style={{ marginTop:14, padding:'12px', background:'#F5F3FF', borderRadius:10, fontSize:13, color:'#7C3AED', fontWeight:600 }}>
              💡 Consider adding a <strong>Stamina</strong> card to cover the Kanto Okami Cup requirements.
            </div>
          </Card>
        </div>

        {/* Card detail */}
        {selected && (
          <Card style={{ padding:22, alignSelf:'start', position:'sticky', top:0 }}>
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:16 }}>
              <div>
                <div style={{ fontSize:17, fontWeight:900, color:'#1E1033' }}>{selected.name}</div>
                <div style={{ display:'flex', gap:6, marginTop:4, alignItems:'center' }}>
                  <RarityBadge rarity={selected.rarity} />
                  <span style={{ fontSize:12, color:'#7C6FAB' }}>{selected.type} Type</span>
                </div>
              </div>
              <button onClick={()=>setSelected(null)} style={{ background:'#F9F5FF', border:'none', borderRadius:8, padding:6, cursor:'pointer' }}>
                <Icon name='x' size={16} color='#7C6FAB' />
              </button>
            </div>

            {/* Bond details */}
            <div style={{ marginBottom:16 }}>
              <div style={{ display:'flex', justifyContent:'space-between', fontSize:13, fontWeight:700, color:'#1E1033', marginBottom:6 }}>
                <span>Bond Progress</span>
                <span style={{ color:typeColor[selected.type], fontWeight:900 }}>{selected.bond}%</span>
              </div>
              <div style={{ height:10, background:'#EDE9FE', borderRadius:99, overflow:'hidden' }}>
                <div style={{ height:'100%', width:`${selected.bond}%`, background:typeColor[selected.type], borderRadius:99 }}/>
              </div>
              <div style={{ display:'flex', justifyContent:'space-between', fontSize:11, color:'#7C6FAB', marginTop:4 }}>
                <span>Current: {selected.bond}%</span>
                <span>Max: 100%</span>
              </div>
            </div>

            {/* Tier badge */}
            <div style={{ display:'flex', gap:10, marginBottom:16 }}>
              <div style={{ flex:1, textAlign:'center', background:'#F9F5FF', borderRadius:10, padding:'12px' }}>
                <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>META TIER</div>
                <div style={{ fontSize:24, fontWeight:900, background:tierBg[selected.tier]||'#EDE9FE', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent' }}>{selected.tier}</div>
              </div>
              <div style={{ flex:1, textAlign:'center', background:'#F9F5FF', borderRadius:10, padding:'12px' }}>
                <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>TRAINS</div>
                <div style={{ fontSize:14, fontWeight:900, color:typeColor[selected.type] }}>{typeIcon[selected.type]} {selected.type}</div>
              </div>
            </div>

            {/* Skills */}
            <div style={{ marginBottom:16 }}>
              <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:8 }}>Skills Provided</div>
              {selected.skills.map((sk,i)=>(
                <div key={i} style={{ display:'flex', gap:8, alignItems:'center', padding:'8px 10px', background:'#F9F5FF', borderRadius:8, marginBottom:6, border:'1px solid #EDE9FE' }}>
                  <span style={{ fontSize:14 }}>✨</span>
                  <span style={{ fontSize:13, fontWeight:700, color:'#1E1033' }}>{sk}</span>
                </div>
              ))}
            </div>

            <Btn variant='secondary' style={{ width:'100%', justifyContent:'center' }}>
              Replace Card
            </Btn>
          </Card>
        )}
      </div>
    </div>
  );
}

Object.assign(window, { SupportCards });
