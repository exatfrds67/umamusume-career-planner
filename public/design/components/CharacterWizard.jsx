
function CharacterWizard({ onClose, onComplete }) {
  const [step, setStep] = React.useState(1);
  const [selections, setSelections] = React.useState({ trainee:null, scenario:'URA Finals', parentA:null, parentB:null, deck:[null,null,null,null,null,null] });
  const [search, setSearch] = React.useState('');
  const [rarityFilter, setRarityFilter] = React.useState('all');

  const TOTAL = 4;

  const CATALOG_SUPPORT = [
    { id:1, name:'Kitasan Black', type:'Power',   rarity:'SSR', tier:'S'  },
    { id:2, name:'Vodka',         type:'Speed',   rarity:'SSR', tier:'A'  },
    { id:3, name:'Gold Ship',     type:'Guts',    rarity:'SSR', tier:'S+' },
    { id:4, name:'Nice Nature',   type:'Stamina', rarity:'SR',  tier:'B'  },
    { id:5, name:'Taiki Shuttle', type:'Speed',   rarity:'SR',  tier:'B'  },
    { id:6, name:'Trainer',       type:'Friend',  rarity:'SSR', tier:'A+' },
    { id:7, name:'Seiun Sky',     type:'Wit',     rarity:'SR',  tier:'A'  },
    { id:8, name:'Haru Urara',    type:'Guts',    rarity:'R',   tier:'C'  },
  ];

  const PARENTS = [
    { id:1, name:'Special Week',   factors:[{ stat:'speed',rating:3,bonus:21 },{ stat:'stamina',rating:1,bonus:5 }] },
    { id:2, name:'Silence Suzuka', factors:[{ stat:'speed',rating:2,bonus:12 },{ stat:'wit',rating:2,bonus:12 }] },
    { id:3, name:'El Condor Pasa', factors:[{ stat:'power',rating:3,bonus:21 },{ stat:'speed',rating:1,bonus:5 }] },
    { id:4, name:'Grass Wonder',   factors:[{ stat:'wit',rating:3,bonus:21 },{ stat:'guts',rating:1,bonus:5 }] },
  ];

  const typeColor = { Speed:'#E879A0', Stamina:'#10B981', Power:'#F59E0B', Guts:'#EF4444', Wit:'#3B82F6', Friend:'#7C3AED' };

  const filteredTrainees = TRAINEES.filter(t =>
    (rarityFilter==='all' || t.rarity===rarityFilter) &&
    t.name.toLowerCase().includes(search.toLowerCase())
  );

  const setDeckSlot = (slotIdx, card) => {
    const newDeck = [...selections.deck];
    newDeck[slotIdx] = card;
    setSelections(s=>({ ...s, deck:newDeck }));
  };

  const [deckSlotTarget, setDeckSlotTarget] = React.useState(null);

  const canAdvance = () => {
    if (step===1) return !!selections.trainee;
    if (step===2) return !!selections.parentA && !!selections.parentB;
    if (step===3) return selections.deck.filter(Boolean).length >= 1;
    return true;
  };

  const StepDot = ({ n }) => (
    <div style={{ display:'flex', flexDirection:'column', alignItems:'center', gap:4 }}>
      <div style={{ width:32, height:32, borderRadius:99, display:'flex', alignItems:'center', justifyContent:'center', fontWeight:800, fontSize:14, background: step>n?'#10B981':step===n?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color: step>=n?'#fff':'#7C6FAB', transition:'all .3s' }}>{step>n?'✓':n}</div>
      <span style={{ fontSize:10, fontWeight:700, color:step===n?'#7C3AED':'#7C6FAB', whiteSpace:'nowrap' }}>{['Trainee','Parents','Support Deck','Review'][n-1]}</span>
    </div>
  );

  return (
    <div style={{ position:'fixed', inset:0, background:'rgba(15,10,40,0.7)', backdropFilter:'blur(8px)', zIndex:100, display:'flex', alignItems:'center', justifyContent:'center', padding:24 }}>
      <div style={{ background:'#fff', borderRadius:24, width:'100%', maxWidth:720, maxHeight:'90vh', display:'flex', flexDirection:'column', overflow:'hidden', boxShadow:'0 24px 80px rgba(124,58,237,0.3)' }}>
        {/* Header */}
        <div style={{ padding:'20px 28px', borderBottom:'1px solid #EDE9FE', display:'flex', alignItems:'center', justifyContent:'space-between', flexShrink:0 }}>
          <div>
            <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>Create New Character</div>
            <div style={{ fontSize:12, color:'#7C6FAB' }}>Step {step} of {TOTAL}</div>
          </div>
          <button onClick={onClose} style={{ background:'#F9F5FF', border:'none', borderRadius:10, padding:8, cursor:'pointer' }}>
            <Icon name='x' size={18} color='#7C6FAB' />
          </button>
        </div>

        {/* Step indicators */}
        <div style={{ padding:'16px 28px', borderBottom:'1px solid #EDE9FE', display:'flex', alignItems:'center', gap:0, flexShrink:0 }}>
          {[1,2,3,4].map((n,i)=>(
            <React.Fragment key={n}>
              <StepDot n={n} />
              {i<3 && <div style={{ flex:1, height:2, background:step>n?'#10B981':'#EDE9FE', margin:'0 4px', marginBottom:20, transition:'background .3s' }}/>}
            </React.Fragment>
          ))}
        </div>

        {/* Body */}
        <div style={{ flex:1, overflowY:'auto', padding:'24px 28px' }}>

          {/* Step 1: Select Trainee */}
          {step===1 && (
            <div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:4 }}>Select Trainee</div>
              <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:16 }}>Choose the Uma Musume you want to train this career run.</div>
              <div style={{ display:'flex', gap:8, marginBottom:12, flexWrap:'wrap' }}>
                <div style={{ position:'relative', flex:1, minWidth:200 }}>
                  <Icon name='search' size={15} color='#7C6FAB' style={{ position:'absolute', left:10, top:'50%', transform:'translateY(-50%)' }}/>
                  <input value={search} onChange={e=>setSearch(e.target.value)} placeholder="Search trainees..." style={{ width:'100%', padding:'9px 12px 9px 34px', borderRadius:10, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:13, color:'#1E1033', background:'#F9F5FF', outline:'none', boxSizing:'border-box' }}/>
                </div>
                {['all','SSR','SR','R'].map(r=>(
                  <button key={r} onClick={()=>setRarityFilter(r)} style={{ padding:'8px 14px', borderRadius:10, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, background:rarityFilter===r?'linear-gradient(135deg,#E879A0,#7C3AED)':'#EDE9FE', color:rarityFilter===r?'#fff':'#7C6FAB' }}>{r}</button>
                ))}
              </div>
              <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(140px,1fr))', gap:10, marginBottom:16 }}>
                {filteredTrainees.map(t=>{
                  const sel = selections.trainee?.id===t.id;
                  return (
                    <div key={t.id} onClick={()=>setSelections(s=>({...s,trainee:t}))} style={{ padding:'14px 12px', borderRadius:12, border:`2px solid ${sel?'#E879A0':'#EDE9FE'}`, background:sel?'linear-gradient(135deg,#FDF2F8,#F5F3FF)':'#F9F5FF', cursor:'pointer', textAlign:'center', transition:'all .15s' }}>
                      <div style={{ fontSize:26, marginBottom:6 }}>🐴</div>
                      <div style={{ fontSize:12, fontWeight:800, color:'#1E1033', marginBottom:4, lineHeight:1.3 }}>{t.name}</div>
                      <div style={{ display:'flex', gap:4, justifyContent:'center', flexWrap:'wrap' }}>
                        <RarityBadge rarity={t.rarity} />
                        <span style={{ fontSize:10, background:STAT_COLORS[t.type.toLowerCase()]+'22', color:STAT_COLORS[t.type.toLowerCase()], borderRadius:6, padding:'2px 6px', fontWeight:700 }}>{t.type}</span>
                      </div>
                    </div>
                  );
                })}
              </div>
              <div>
                <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:8 }}>Scenario</div>
                <div style={{ display:'flex', gap:8 }}>
                  {['URA Finals','Unity Cup'].map(sc=>(
                    <button key={sc} onClick={()=>setSelections(s=>({...s,scenario:sc}))} style={{ flex:1, padding:'12px', borderRadius:12, border:`2px solid ${selections.scenario===sc?'#7C3AED':'#EDE9FE'}`, background:selections.scenario===sc?'#F5F3FF':'#F9F5FF', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:13, fontWeight:700, color:selections.scenario===sc?'#7C3AED':'#7C6FAB' }}>
                      {sc==='URA Finals'?'🏆':'🤝'} {sc}
                    </button>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Step 2: Parent Selection */}
          {step===2 && (
            <div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:4 }}>Parent Selection</div>
              <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>Choose Parent A and Parent B for factor inheritance bonuses.</div>
              {['parentA','parentB'].map((key,ki)=>(
                <div key={key} style={{ marginBottom:20 }}>
                  <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:8 }}>Parent {ki===0?'A':'B'}</div>
                  <div style={{ display:'grid', gridTemplateColumns:'repeat(2,1fr)', gap:10 }}>
                    {PARENTS.map(p=>{
                      const sel = selections[key]?.id===p.id;
                      return (
                        <div key={p.id} onClick={()=>setSelections(s=>({...s,[key]:p}))} style={{ padding:'14px', borderRadius:12, border:`2px solid ${sel?'#E879A0':'#EDE9FE'}`, background:sel?'#FDF2F8':'#F9F5FF', cursor:'pointer', transition:'all .15s' }}>
                          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:8 }}>{p.name}</div>
                          <div style={{ display:'flex', gap:6, flexWrap:'wrap' }}>
                            {p.factors.map((f,fi)=>(
                              <div key={fi} style={{ background:'#fff', borderRadius:8, padding:'4px 10px', border:'1px solid #EDE9FE', fontSize:12 }}>
                                <span style={{ fontWeight:700, color:STAT_COLORS[f.stat] }}>{STAT_ICONS[f.stat]} +{f.bonus}</span>
                                <span style={{ color:'#7C6FAB' }}> ({[...Array(f.rating)].map(()=>'★').join('')})</span>
                              </div>
                            ))}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              ))}
              {selections.parentA && selections.parentB && (
                <div style={{ background:'linear-gradient(135deg,#F9F5FF,#EDE9FE)', borderRadius:12, padding:'14px', border:'1px solid #C4B5FD' }}>
                  <div style={{ fontSize:12, fontWeight:800, color:'#7C3AED', marginBottom:8 }}>Combined Inheritance Preview</div>
                  <div style={{ display:'flex', gap:8, flexWrap:'wrap' }}>
                    {[...selections.parentA.factors, ...selections.parentB.factors].map((f,i)=>(
                      <span key={i} style={{ background:'#fff', borderRadius:8, padding:'4px 12px', fontSize:13, fontWeight:800, color:STAT_COLORS[f.stat], border:'1px solid #EDE9FE' }}>
                        {STAT_ICONS[f.stat]} +{f.bonus}
                      </span>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Step 3: Support Deck */}
          {step===3 && (
            <div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:4 }}>Support Deck</div>
              <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>Build your 6-card support deck. Click a slot then pick a card.</div>
              <div style={{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:10, marginBottom:20 }}>
                {selections.deck.map((card,i)=>(
                  <div key={i} onClick={()=>setDeckSlotTarget(deckSlotTarget===i?null:i)} style={{ borderRadius:12, border:`2px solid ${deckSlotTarget===i?'#E879A0':card?'#C4B5FD':'#EDE9FE'}`, background:card?'#F5F3FF':'#F9F5FF', padding:'12px', cursor:'pointer', minHeight:80, display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center', textAlign:'center', transition:'all .15s' }}>
                    {card ? (
                      <>
                        <div style={{ fontSize:11, fontWeight:800, color:typeColor[card.type]||'#7C3AED', marginBottom:2 }}>{card.type}</div>
                        <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', lineHeight:1.3 }}>{card.name}</div>
                        <RarityBadge rarity={card.rarity} />
                      </>
                    ) : (
                      <>
                        <div style={{ fontSize:20, color:'#C4B5FD', marginBottom:4 }}>+</div>
                        <div style={{ fontSize:11, color:'#7C6FAB' }}>Slot {i+1}</div>
                      </>
                    )}
                  </div>
                ))}
              </div>
              {deckSlotTarget!==null && (
                <div>
                  <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:8 }}>Pick card for Slot {deckSlotTarget+1}</div>
                  <div style={{ display:'grid', gridTemplateColumns:'repeat(2,1fr)', gap:8 }}>
                    {CATALOG_SUPPORT.map(c=>(
                      <div key={c.id} onClick={()=>{ setDeckSlot(deckSlotTarget,c); setDeckSlotTarget(null); }} style={{ display:'flex', gap:10, padding:'10px 12px', borderRadius:10, background:'#F9F5FF', border:'1px solid #EDE9FE', cursor:'pointer', alignItems:'center' }}>
                        <div style={{ width:36, height:36, borderRadius:10, background:typeColor[c.type]+'22', display:'flex', alignItems:'center', justifyContent:'center', fontSize:18 }}>🃏</div>
                        <div style={{ flex:1 }}>
                          <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{c.name}</div>
                          <div style={{ display:'flex', gap:4, marginTop:2 }}>
                            <span style={{ fontSize:10, background:typeColor[c.type]+'22', color:typeColor[c.type], borderRadius:6, padding:'1px 6px', fontWeight:700 }}>{c.type}</span>
                            <RarityBadge rarity={c.rarity} />
                            <span style={{ fontSize:10, color:'#7C6FAB' }}>Tier {c.tier}</span>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Step 4: Review */}
          {step===4 && (
            <div>
              <div style={{ fontSize:16, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Review & Confirm</div>
              {[
                { label:'Trainee', value: selections.trainee ? `${selections.trainee.name} (${selections.trainee.rarity})` : '—' },
                { label:'Scenario', value: selections.scenario },
                { label:'Parent A', value: selections.parentA?.name || '—' },
                { label:'Parent B', value: selections.parentB?.name || '—' },
                { label:'Deck', value: `${selections.deck.filter(Boolean).length}/6 cards filled` },
              ].map(row=>(
                <div key={row.label} style={{ display:'flex', justifyContent:'space-between', padding:'12px 0', borderBottom:'1px solid #EDE9FE' }}>
                  <span style={{ fontSize:13, color:'#7C6FAB', fontWeight:700 }}>{row.label}</span>
                  <span style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{row.value}</span>
                </div>
              ))}
              <div style={{ marginTop:20, background:'linear-gradient(135deg,#F9F5FF,#EDE9FE)', borderRadius:14, padding:'16px', border:'1px solid #C4B5FD', textAlign:'center' }}>
                <div style={{ fontSize:18 }}>🎉</div>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginTop:6 }}>Ready to start your career run!</div>
                <div style={{ fontSize:12, color:'#7C6FAB', marginTop:4 }}>Your character will be initialized at Turn 1 with starting stats, mood, and energy.</div>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div style={{ padding:'16px 28px', borderTop:'1px solid #EDE9FE', display:'flex', justifyContent:'space-between', flexShrink:0 }}>
          <Btn variant='ghost' onClick={step===1?onClose:()=>setStep(s=>s-1)}>
            {step===1 ? 'Cancel' : '← Back'}
          </Btn>
          <Btn variant={step===4?'gold':'primary'} onClick={step===4?onComplete:()=>setStep(s=>s+1)} disabled={!canAdvance()}>
            {step===4 ? '🎉 Create Character' : 'Next →'}
          </Btn>
        </div>
      </div>
    </div>
  );
}

Object.assign(window, { CharacterWizard });
