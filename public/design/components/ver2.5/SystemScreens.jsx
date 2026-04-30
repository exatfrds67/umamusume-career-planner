
// ── APM Performance Dashboard (SPEC-008) ─────────────────────────────────────
function Performance() {
  const [tab, setTab] = React.useState('overview');
  const [alertsExpanded, setAlertsExpanded] = React.useState(false);

  // Simulated APM data
  const API_ENDPOINTS = [
    { route:'/api/training/predictions', method:'GET', avg:84, p95:142, p99:280, rpm:320, errors:0.2, cached:72 },
    { route:'/api/advisory/training',    method:'POST',avg:420, p95:890, p99:1800,rpm:85, errors:1.1, cached:15 },
    { route:'/api/ai/chat/message',      method:'POST',avg:1240,p95:3200,p99:5800,rpm:42, errors:0.8, cached:0  },
    { route:'/api/races/strategy',       method:'GET', avg:112, p95:195, p99:310, rpm:180,errors:0.1, cached:88 },
    { route:'/api/skills/catalog',       method:'GET', avg:48,  p95:82,  p99:140, rpm:210,errors:0.0, cached:95 },
    { route:'/api/support-deck/validate',method:'POST',avg:96,  p95:180, p99:290, rpm:64, errors:0.3, cached:0  },
  ];

  const DB_QUERIES = [
    { query:'career.trainingSessions eager load', time:42, status:'ok',      n1:false },
    { query:'Character with aptitudes + factors', time:28, status:'ok',      n1:false },
    { query:'RaceResult aggregate by character',  time:185,status:'warning', n1:false },
    { query:'SkillAcquisition hint level scan',   time:310,status:'slow',    n1:true  },
    { query:'SupportDeck with card definitions',  time:35, status:'ok',      n1:false },
  ];

  const CACHE_STATS = [
    { key:'training_predictions',  hits:4821, misses:892, hitRate:84, ttl:'5m' },
    { key:'race_strategy_cache',   hits:3204, misses:180, hitRate:95, ttl:'10m'},
    { key:'skill_catalog',         hits:8920, misses:45,  hitRate:99, ttl:'1h' },
    { key:'character_report',      hits:1230, misses:420, hitRate:75, ttl:'15m'},
    { key:'external_api_umapyoi',  hits:640,  misses:84,  hitRate:88, ttl:'24h'},
  ];

  const ALERTS = [
    { id:1, level:'warning', service:'AiAdvisoryService',    msg:'Avg response time 420ms — above 200ms threshold', time:'2m ago',  acked:false },
    { id:2, level:'info',    service:'RedisCacheService',    msg:'character_report hit rate dropped to 75% — consider TTL increase', time:'15m ago', acked:false },
    { id:3, level:'error',   service:'QueryOptimization',    msg:'N+1 detected: SkillAcquisition hint level scan', time:'1h ago',  acked:true  },
    { id:4, level:'info',    service:'PerformanceRegression',msg:'Training prediction latency improved 12% vs last deploy', time:'3h ago',  acked:true  },
  ];

  const REGRESSION = [
    { deploy:'v2.4.2', date:'Apr 21', avgMs:84,  delta:-8,  status:'improved' },
    { deploy:'v2.4.1', date:'Apr 14', avgMs:92,  delta:+4,  status:'regression' },
    { deploy:'v2.4.0', date:'Apr 7',  avgMs:88,  delta:-12, status:'improved' },
    { deploy:'v2.3.9', date:'Mar 31', avgMs:100, delta:+15, status:'regression' },
  ];

  const alertColor = { error:'#EF4444', warning:'#F59E0B', info:'#3B82F6' };
  const alertBg    = { error:'#FEF2F2', warning:'#FFFBEB', info:'#EFF6FF' };
  const statusColor = { ok:'#10B981', warning:'#F59E0B', slow:'#EF4444' };

  const MiniBar = ({ val, max, color }) => (
    <div style={{ height:5, background:'#EDE9FE', borderRadius:99, flex:1 }}>
      <div style={{ height:'100%', width:`${Math.min(100,(val/max)*100)}%`, background:color, borderRadius:99 }}/>
    </div>
  );

  return (
    <div>
      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 }}>
        <div>
          <div style={{ fontSize:22, fontWeight:900, color:'#1E1033' }}>Performance Dashboard</div>
          <div style={{ fontSize:13, color:'#7C6FAB' }}>ApmService · ApiPerformanceMonitoringService · SPEC-008</div>
        </div>
        <div style={{ display:'flex', gap:8, alignItems:'center' }}>
          <div style={{ background:'#D1FAE5', borderRadius:10, padding:'7px 14px', fontSize:12, fontWeight:800, color:'#065F46', border:'1px solid #6EE7B7' }}>⚡ 84ms avg</div>
          <div style={{ background:'#D1FAE5', borderRadius:10, padding:'7px 14px', fontSize:12, fontWeight:800, color:'#065F46', border:'1px solid #6EE7B7' }}>✅ 99.9% uptime</div>
          <button onClick={()=>setAlertsExpanded(!alertsExpanded)} style={{ position:'relative', background:alertsExpanded?'#EDE9FE':'#F9F5FF', border:'1px solid #EDE9FE', borderRadius:10, padding:'7px 14px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, color:'#7C3AED' }}>
            🔔 Alerts
            <span style={{ position:'absolute', top:-4, right:-4, width:18, height:18, borderRadius:99, background:'#EF4444', color:'#fff', fontSize:9, fontWeight:900, display:'flex', alignItems:'center', justifyContent:'center' }}>
              {ALERTS.filter(a=>!a.acked).length}
            </span>
          </button>
        </div>
      </div>

      {/* Alert panel */}
      {alertsExpanded && (
        <Card style={{ padding:16, marginBottom:20, border:'1px solid #EDE9FE' }}>
          <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:12 }}>Active Alerts · PerformanceAlertingService</div>
          {ALERTS.map(a=>(
            <div key={a.id} style={{ display:'flex', gap:10, alignItems:'flex-start', padding:'10px 12px', background:alertBg[a.level], borderRadius:10, marginBottom:6, border:`1px solid ${alertColor[a.level]}22`, opacity:a.acked?.6:1 }}>
              <div style={{ width:8, height:8, borderRadius:99, background:alertColor[a.level], flexShrink:0, marginTop:4 }}/>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:12, fontWeight:800, color:'#1E1033' }}>{a.service}</div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>{a.msg}</div>
              </div>
              <div style={{ fontSize:10, color:'#9CA3AF', whiteSpace:'nowrap' }}>{a.time}</div>
              {a.acked && <span style={{ fontSize:10, background:'#EDE9FE', color:'#7C3AED', borderRadius:6, padding:'1px 7px', fontWeight:700 }}>Acked</span>}
            </div>
          ))}
        </Card>
      )}

      {/* Summary stats */}
      <div style={{ display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:12, marginBottom:24 }}>
        {[
          { label:'Avg Response',  val:'84ms',  sub:'< 200ms ✓', color:'#10B981', bg:'#F0FDF4' },
          { label:'P95 Latency',   val:'195ms', sub:'< 500ms ✓', color:'#7C3AED', bg:'#F5F3FF' },
          { label:'Cache Hit Rate',val:'88%',   sub:'Redis warm',  color:'#E879A0', bg:'#FDF2F8' },
          { label:'Req / min',     val:'901',   sub:'Normal load', color:'#F59E0B', bg:'#FFFBEB' },
          { label:'Error Rate',    val:'0.4%',  sub:'< 1% ✓',     color:'#10B981', bg:'#F0FDF4' },
        ].map(item=>(
          <Card key={item.label} style={{ padding:16, background:item.bg, border:`1px solid ${item.color}22` }}>
            <div style={{ fontSize:11, fontWeight:700, color:'#7C6FAB' }}>{item.label}</div>
            <div style={{ fontSize:22, fontWeight:900, color:item.color, marginTop:4 }}>{item.val}</div>
            <div style={{ fontSize:10, color:'#9CA3AF', marginTop:2 }}>{item.sub}</div>
          </Card>
        ))}
      </div>

      {/* Tabs */}
      <div style={{ display:'flex', gap:4, marginBottom:20, background:'#EDE9FE', borderRadius:12, padding:4, width:'fit-content' }}>
        {['overview','endpoints','database','cache','regression'].map(t=>(
          <button key={t} onClick={()=>setTab(t)} style={{ padding:'7px 16px', borderRadius:9, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:12, fontWeight:700, textTransform:'capitalize', background:tab===t?'#fff':'transparent', color:tab===t?'#7C3AED':'#7C6FAB', boxShadow:tab===t?'0 1px 6px rgba(124,58,237,0.15)':'' }}>{t}</button>
        ))}
      </div>

      {tab==='overview' && (
        <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20 }}>
          <Card style={{ padding:22 }}>
            <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Service Components · ApmService</div>
            {[
              { svc:'ApiPerformanceMonitoringService', status:'healthy',  latency:'84ms' },
              { svc:'QueryOptimizationService',        status:'warning',  latency:'185ms' },
              { svc:'PerformanceAlertingService',      status:'healthy',  latency:'<1ms' },
              { svc:'RedisCacheOptimizationService',   status:'healthy',  latency:'2ms' },
              { svc:'PerformanceRegressionService',    status:'healthy',  latency:'N/A' },
              { svc:'HistoricalTrackingService',       status:'healthy',  latency:'N/A' },
            ].map(s=>(
              <div key={s.svc} style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'9px 0', borderBottom:'1px solid #F9F5FF' }}>
                <div style={{ display:'flex', gap:8, alignItems:'center' }}>
                  <div style={{ width:8, height:8, borderRadius:99, background:s.status==='healthy'?'#10B981':'#F59E0B' }}/>
                  <span style={{ fontSize:12, fontWeight:600, color:'#1E1033', fontFamily:'monospace' }}>{s.svc}</span>
                </div>
                <span style={{ fontSize:11, fontWeight:800, color:s.status==='healthy'?'#10B981':'#F59E0B' }}>{s.latency}</span>
              </div>
            ))}
          </Card>
          <Card style={{ padding:22 }}>
            <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Technology Stack</div>
            {[
              { layer:'Metrics Collection',  tech:'Laravel Middleware',  note:'Request-level instrumentation' },
              { layer:'Real-time Storage',   tech:'Redis',               note:'Hot metrics, 1h retention' },
              { layer:'Historical Storage',  tech:'MySQL',               note:'Cold storage, tiered retention' },
              { layer:'Query Analysis',      tech:'EXPLAIN + slow log',  note:'DB optimization' },
              { layer:'Alerting',            tech:'Laravel Events',      note:'Multi-channel notifications' },
              { layer:'Visualization',       tech:'Telescope + Custom',  note:'Metrics display' },
            ].map(row=>(
              <div key={row.layer} style={{ padding:'8px 0', borderBottom:'1px solid #F9F5FF' }}>
                <div style={{ display:'flex', justifyContent:'space-between', marginBottom:2 }}>
                  <span style={{ fontSize:12, fontWeight:700, color:'#7C6FAB' }}>{row.layer}</span>
                  <span style={{ fontSize:12, fontWeight:800, color:'#7C3AED' }}>{row.tech}</span>
                </div>
                <div style={{ fontSize:10, color:'#9CA3AF' }}>{row.note}</div>
              </div>
            ))}
          </Card>
        </div>
      )}

      {tab==='endpoints' && (
        <Card style={{ padding:22 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:16 }}>API Endpoint Performance · ApiPerformanceMonitoringService</div>
          <div style={{ display:'grid', gridTemplateColumns:'2fr repeat(6,1fr)', gap:8, padding:'8px 12px', background:'#F9F5FF', borderRadius:8, marginBottom:8, fontSize:11, fontWeight:800, color:'#7C6FAB' }}>
            <span>Endpoint</span><span>Avg</span><span>P95</span><span>P99</span><span>RPM</span><span>Errors</span><span>Cached</span>
          </div>
          {API_ENDPOINTS.map((ep,i)=>(
            <div key={i} style={{ display:'grid', gridTemplateColumns:'2fr repeat(6,1fr)', gap:8, padding:'10px 12px', background:'#fff', borderRadius:8, marginBottom:4, border:'1px solid #EDE9FE', alignItems:'center' }}>
              <div>
                <span style={{ fontSize:9, fontWeight:800, background:'#EDE9FE', color:'#7C3AED', borderRadius:4, padding:'1px 5px', marginRight:6 }}>{ep.method}</span>
                <span style={{ fontSize:11, fontFamily:'monospace', color:'#1E1033' }}>{ep.route}</span>
              </div>
              <span style={{ fontSize:12, fontWeight:900, color:ep.avg<200?'#10B981':ep.avg<500?'#F59E0B':'#EF4444' }}>{ep.avg}ms</span>
              <span style={{ fontSize:11, color:'#7C6FAB' }}>{ep.p95}ms</span>
              <span style={{ fontSize:11, color:'#7C6FAB' }}>{ep.p99}ms</span>
              <span style={{ fontSize:11, color:'#7C6FAB' }}>{ep.rpm}</span>
              <span style={{ fontSize:11, fontWeight:700, color:ep.errors<1?'#10B981':'#EF4444' }}>{ep.errors}%</span>
              <div style={{ display:'flex', alignItems:'center', gap:6 }}>
                <MiniBar val={ep.cached} max={100} color='#10B981'/>
                <span style={{ fontSize:10, fontWeight:700, color:'#10B981', minWidth:28 }}>{ep.cached}%</span>
              </div>
            </div>
          ))}
        </Card>
      )}

      {tab==='database' && (
        <Card style={{ padding:22 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Query Analysis · QueryOptimizationService</div>
          {DB_QUERIES.map((q,i)=>(
            <div key={i} style={{ display:'flex', gap:14, alignItems:'center', padding:'12px 14px', background:q.status==='slow'?'#FEF2F2':q.status==='warning'?'#FFFBEB':'#F9F5FF', borderRadius:10, marginBottom:8, border:`1px solid ${statusColor[q.status]}22` }}>
              <div style={{ width:10, height:10, borderRadius:99, background:statusColor[q.status], flexShrink:0 }}/>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:13, fontWeight:700, color:'#1E1033', fontFamily:'monospace' }}>{q.query}</div>
                {q.n1 && <div style={{ fontSize:11, fontWeight:800, color:'#EF4444', marginTop:2 }}>⚠️ N+1 detected — add eager loading</div>}
              </div>
              <span style={{ fontSize:14, fontWeight:900, color:statusColor[q.status] }}>{q.time}ms</span>
              <span style={{ fontSize:11, background:`${statusColor[q.status]}18`, color:statusColor[q.status], borderRadius:6, padding:'2px 8px', fontWeight:800, textTransform:'uppercase' }}>{q.status}</span>
            </div>
          ))}
          <div style={{ marginTop:12, padding:'12px', background:'#FEF3C7', borderRadius:10, fontSize:12, color:'#92400E', fontWeight:700 }}>
            ⚠️ 1 N+1 query detected · Lazy loading in loops is prohibited for report rendering and export preparation
          </div>
        </Card>
      )}

      {tab==='cache' && (
        <Card style={{ padding:22 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Redis Cache Analytics · RedisCacheOptimizationService</div>
          {CACHE_STATS.map((c,i)=>{
            const hr = c.hitRate;
            const color = hr>=90?'#10B981':hr>=75?'#F59E0B':'#EF4444';
            return (
              <div key={i} style={{ marginBottom:14 }}>
                <div style={{ display:'flex', justifyContent:'space-between', marginBottom:6, alignItems:'center' }}>
                  <span style={{ fontSize:13, fontWeight:700, color:'#1E1033', fontFamily:'monospace' }}>{c.key}</span>
                  <div style={{ display:'flex', gap:10, alignItems:'center' }}>
                    <span style={{ fontSize:11, color:'#7C6FAB' }}>TTL: {c.ttl}</span>
                    <span style={{ fontSize:12, fontWeight:900, color }}>Hit: {c.hitRate}%</span>
                  </div>
                </div>
                <div style={{ height:8, background:'#EDE9FE', borderRadius:99, marginBottom:4, overflow:'hidden' }}>
                  <div style={{ height:'100%', width:`${hr}%`, background:color, borderRadius:99 }}/>
                </div>
                <div style={{ display:'flex', gap:16, fontSize:11, color:'#7C6FAB' }}>
                  <span>✅ {c.hits.toLocaleString()} hits</span>
                  <span>❌ {c.misses.toLocaleString()} misses</span>
                  <span>Total: {(c.hits+c.misses).toLocaleString()}</span>
                </div>
              </div>
            );
          })}
        </Card>
      )}

      {tab==='regression' && (
        <Card style={{ padding:22 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Regression Detection · PerformanceRegressionService</div>
          {REGRESSION.map((r,i)=>(
            <div key={i} style={{ display:'flex', gap:14, alignItems:'center', padding:'12px 16px', background:r.status==='improved'?'#F0FDF4':'#FEF2F2', borderRadius:10, marginBottom:8, border:`1px solid ${r.status==='improved'?'#BBF7D0':'#FECACA'}` }}>
              <div style={{ width:40, height:40, borderRadius:10, background:r.status==='improved'?'#D1FAE5':'#FEE2E2', display:'flex', alignItems:'center', justifyContent:'center', fontSize:20 }}>
                {r.status==='improved'?'📈':'📉'}
              </div>
              <div style={{ flex:1 }}>
                <div style={{ fontSize:14, fontWeight:800, color:'#1E1033' }}>{r.deploy}</div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>{r.date}</div>
              </div>
              <div style={{ textAlign:'right' }}>
                <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>{r.avgMs}ms</div>
                <div style={{ fontSize:12, fontWeight:800, color:r.delta<0?'#10B981':'#EF4444' }}>{r.delta>0?'+':''}{r.delta}ms vs prev</div>
              </div>
              <span style={{ fontSize:12, fontWeight:800, background:r.status==='improved'?'#D1FAE5':'#FEE2E2', color:r.status==='improved'?'#065F46':'#991B1B', borderRadius:8, padding:'4px 12px', textTransform:'capitalize' }}>{r.status}</span>
            </div>
          ))}
        </Card>
      )}
    </div>
  );
}

// ── External Sync Status (PRD-007 §5.2) ───────────────────────────────────────
function ExternalSync() {
  const [syncing, setSyncing] = React.useState(null);
  const [cbState, setCbState] = React.useState({ umapyoi:'closed', umamusumedb:'closed' });

  const PROVIDERS = [
    {
      id:'umapyoi', name:'umapyoi.net', role:'Primary',
      status:'online', lastSync:'2 min ago', nextSync:'58 min',
      ttl:'24h', cached:640, records:18420, success:99.2,
      color:'#10B981', bg:'#F0FDF4', border:'#6EE7B7',
    },
    {
      id:'umamusumedb', name:'umamusumedb.com', role:'Fallback',
      status:'online', lastSync:'1 hr ago', nextSync:'23 hr',
      ttl:'24h', cached:184, records:12080, success:97.8,
      color:'#7C3AED', bg:'#F5F3FF', border:'#C4B5FD',
    },
  ];

  const SYNC_JOBS = [
    { id:1, provider:'umapyoi.net',     type:'Support Cards Catalog',  status:'success', time:'2 min ago',  records:48,    duration:'1.2s' },
    { id:2, provider:'umapyoi.net',     type:'Game Mechanics Data',    status:'success', time:'1 hr ago',   records:312,   duration:'3.4s' },
    { id:3, provider:'umamusumedb.com', type:'Race Calendar Update',   status:'success', time:'1 hr ago',   records:96,    duration:'2.1s' },
    { id:4, provider:'umapyoi.net',     type:'Uma Musume Character Data',status:'failed',time:'6 hr ago',   records:0,     duration:'timeout' },
    { id:5, provider:'umamusumedb.com', type:'Support Card Definitions',status:'success',time:'12 hr ago',  records:1240,  duration:'8.2s' },
  ];

  const triggerSync = (id) => {
    setSyncing(id);
    setTimeout(()=>setSyncing(null), 2200);
  };

  const toggleCB = (id) => {
    setCbState(s=>({ ...s, [id]: s[id]==='closed'?'open':s[id]==='open'?'half-open':'closed' }));
  };

  const cbColor = { closed:'#10B981', open:'#EF4444', 'half-open':'#F59E0B' };

  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>External Data Sync</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>umapyoi.net · umamusumedb.com · Circuit Breaker · PRD-007</div>

      {/* Provider cards */}
      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginBottom:24 }}>
        {PROVIDERS.map(p=>(
          <Card key={p.id} style={{ padding:22, background:p.bg, border:`1px solid ${p.border}` }}>
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:16 }}>
              <div>
                <div style={{ display:'flex', gap:8, alignItems:'center', marginBottom:4 }}>
                  <div style={{ width:10, height:10, borderRadius:99, background:p.color, boxShadow:`0 0 6px ${p.color}` }}/>
                  <span style={{ fontSize:16, fontWeight:900, color:'#1E1033' }}>{p.name}</span>
                  <span style={{ fontSize:11, background:p.color+'22', color:p.color, borderRadius:6, padding:'1px 8px', fontWeight:800 }}>{p.role}</span>
                </div>
                <div style={{ fontSize:12, color:'#7C6FAB' }}>Last sync: <strong>{p.lastSync}</strong> · Next: {p.nextSync}</div>
              </div>
              <Btn variant={syncing===p.id?'secondary':'primary'} size='sm' onClick={()=>triggerSync(p.id)}>
                {syncing===p.id ? '⏳ Syncing…' : '🔄 Sync Now'}
              </Btn>
            </div>

            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8, marginBottom:14 }}>
              {[
                { label:'Cached',  val:p.cached.toLocaleString(), icon:'💾' },
                { label:'Records', val:p.records.toLocaleString(), icon:'📋' },
                { label:'Success', val:`${p.success}%`, icon:'✅' },
              ].map(item=>(
                <div key={item.label} style={{ background:'rgba(255,255,255,0.7)', borderRadius:8, padding:'8px', textAlign:'center' }}>
                  <div style={{ fontSize:10, color:'#7C6FAB', fontWeight:700 }}>{item.icon} {item.label}</div>
                  <div style={{ fontSize:15, fontWeight:900, color:p.color }}>{item.val}</div>
                </div>
              ))}
            </div>

            {/* Circuit breaker */}
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', padding:'10px 12px', background:'rgba(255,255,255,0.6)', borderRadius:10 }}>
              <div>
                <div style={{ fontSize:11, fontWeight:800, color:'#7C6FAB' }}>CIRCUIT BREAKER</div>
                <div style={{ display:'flex', gap:6, alignItems:'center', marginTop:2 }}>
                  <div style={{ width:8, height:8, borderRadius:99, background:cbColor[cbState[p.id]], boxShadow:`0 0 4px ${cbColor[cbState[p.id]]}` }}/>
                  <span style={{ fontSize:13, fontWeight:900, color:cbColor[cbState[p.id]], textTransform:'uppercase' }}>{cbState[p.id]}</span>
                </div>
                <div style={{ fontSize:10, color:'#7C6FAB', marginTop:2 }}>
                  {cbState[p.id]==='closed'?'Healthy — requests passing through':cbState[p.id]==='open'?'Open — requests blocked, serving cache':'Half-open — testing connectivity'}
                </div>
              </div>
              <div style={{ display:'flex', gap:4 }}>
                {['closed','open','half-open'].map(s=>(
                  <button key={s} onClick={()=>setCbState(cs=>({...cs,[p.id]:s}))} style={{ padding:'3px 7px', borderRadius:5, border:'none', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:9, fontWeight:800, background:cbState[p.id]===s?cbColor[s]:'#EDE9FE', color:cbState[p.id]===s?'#fff':'#7C6FAB', textTransform:'capitalize' }}>{s}</button>
                ))}
              </div>
            </div>
          </Card>
        ))}
      </div>

      {/* Cache TTL strip */}
      <Card style={{ padding:18, marginBottom:20, background:'#F9F5FF' }}>
        <div style={{ fontSize:13, fontWeight:800, color:'#1E1033', marginBottom:12 }}>Cache TTL Status · Redis 24h · ApiResponseCachingService</div>
        <div style={{ display:'flex', gap:10 }}>
          {[
            { key:'Support Cards Catalog', ttl:1440, remaining:1382 },
            { key:'Game Mechanics Data',   ttl:1440, remaining:840  },
            { key:'Race Calendar',         ttl:1440, remaining:1380 },
            { key:'Character Catalog',     ttl:1440, remaining:92   },
          ].map(item=>{
            const pct = Math.round((item.remaining/item.ttl)*100);
            const color = pct>50?'#10B981':pct>20?'#F59E0B':'#EF4444';
            return (
              <div key={item.key} style={{ flex:1, background:'#fff', borderRadius:10, padding:'12px', border:'1px solid #EDE9FE' }}>
                <div style={{ fontSize:11, fontWeight:700, color:'#7C6FAB', marginBottom:6 }}>{item.key}</div>
                <div style={{ height:6, background:'#EDE9FE', borderRadius:99, marginBottom:4 }}>
                  <div style={{ height:'100%', width:`${pct}%`, background:color, borderRadius:99 }}/>
                </div>
                <div style={{ fontSize:11, fontWeight:800, color }}>{Math.round(item.remaining/60)}h {item.remaining%60}m remaining</div>
              </div>
            );
          })}
        </div>
      </Card>

      {/* Sync job history */}
      <Card style={{ padding:22 }}>
        <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:16 }}>Sync Job History · Queue-backed status delivery</div>
        {SYNC_JOBS.map(job=>(
          <div key={job.id} style={{ display:'flex', gap:12, alignItems:'center', padding:'12px 14px', background:job.status==='failed'?'#FEF2F2':'#F9F5FF', borderRadius:10, marginBottom:8, border:`1px solid ${job.status==='failed'?'#FECACA':'#EDE9FE'}` }}>
            <span style={{ fontSize:18 }}>{job.status==='success'?'✅':'❌'}</span>
            <div style={{ flex:1 }}>
              <div style={{ fontSize:13, fontWeight:800, color:'#1E1033' }}>{job.type}</div>
              <div style={{ fontSize:11, color:'#7C6FAB' }}>{job.provider} · {job.time}</div>
            </div>
            <div style={{ textAlign:'right' }}>
              <div style={{ fontSize:12, fontWeight:800, color:job.status==='success'?'#10B981':'#EF4444' }}>{job.records>0?`${job.records} records`:'Failed'}</div>
              <div style={{ fontSize:11, color:'#7C6FAB' }}>{job.duration}</div>
            </div>
          </div>
        ))}
      </Card>
    </div>
  );
}

// ── AI Budget Tracker (PRD-006 §4.1) ─────────────────────────────────────────
function AIBudget() {
  const [dailyLimit, setDailyLimit] = React.useState(5.00);
  const [editing, setEditing] = React.useState(false);
  const [draftLimit, setDraftLimit] = React.useState('5.00');

  const DAILY_SPEND = 1.84;
  const spendPct = Math.min(100, Math.round((DAILY_SPEND/dailyLimit)*100));
  const spendColor = spendPct>=90?'#EF4444':spendPct>=70?'#F59E0B':'#10B981';

  const PROVIDER_BREAKDOWN = [
    { provider:'AWS Bedrock (claude-haiku-4-5)', queries:42, tokens:98420, cost:1.48, color:'#7C3AED', pct:80 },
    { provider:'Local Ollama (llama3.2)',        queries:128,tokens:0,     cost:0.00, color:'#10B981', pct:0  },
    { provider:'Cached responses',              queries:84, tokens:0,     cost:0.00, color:'#E879A0', pct:0  },
  ];

  const ROUTING_LOG = [
    { time:'2 min ago', query:'What should I train next turn?',      route:'Ollama', reason:'Simple query', tokens:0,    cost:0     },
    { time:'8 min ago', query:'Optimize my deck for Kanto Okami…',   route:'Bedrock',reason:'Complex strategy', tokens:2840, cost:0.042 },
    { time:'14 min ago',query:'Am I on track for URA Finals?',        route:'Bedrock',reason:'Complex analysis', tokens:3120, cost:0.047 },
    { time:'22 min ago',query:'Which skills with 450 SP?',            route:'Ollama', reason:'Simple query', tokens:0,    cost:0     },
    { time:'35 min ago',query:'Compare Mile Cup vs Kanto Okami',      route:'Bedrock',reason:'Complex comparison', tokens:2680,cost:0.040 },
  ];

  const WEEKLY = [
    { day:'Mon', spend:2.10 }, { day:'Tue', spend:1.65 }, { day:'Wed', spend:3.20 },
    { day:'Thu', spend:0.95 }, { day:'Fri', spend:2.44 }, { day:'Sat', spend:1.12 },
    { day:'Sun', spend:1.84 },
  ];
  const maxSpend = Math.max(...WEEKLY.map(d=>d.spend));

  return (
    <div>
      <div style={{ fontSize:22, fontWeight:900, color:'#1E1033', marginBottom:4 }}>AI Budget Tracker</div>
      <div style={{ fontSize:13, color:'#7C6FAB', marginBottom:20 }}>AgentRoutingService · Token usage · Cost control · PRD-006 §4.1</div>

      {/* Daily budget */}
      <Card style={{ padding:22, marginBottom:20, background:`linear-gradient(135deg,${spendPct>=90?'#FEF2F2,#FEE2E2':spendPct>=70?'#FFFBEB,#FEF3C7':'#F0FDF4,#D1FAE5'}`, border:`1px solid ${spendColor}33` }}>
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:16 }}>
          <div>
            <div style={{ fontSize:15, fontWeight:800, color:'#1E1033' }}>Daily Bedrock Budget</div>
            <div style={{ fontSize:12, color:'#7C6FAB', marginTop:2 }}>System stops cloud routing when limit is exceeded · US-6.5</div>
          </div>
          <div style={{ textAlign:'right' }}>
            {editing ? (
              <div style={{ display:'flex', gap:6, alignItems:'center' }}>
                <span style={{ fontSize:14, color:'#7C6FAB' }}>$</span>
                <input value={draftLimit} onChange={e=>setDraftLimit(e.target.value)} style={{ width:60, padding:'4px 8px', borderRadius:8, border:'1px solid #EDE9FE', fontFamily:'Nunito,sans-serif', fontSize:16, fontWeight:900, color:'#1E1033', outline:'none' }}/>
                <Btn variant='primary' size='sm' onClick={()=>{ setDailyLimit(parseFloat(draftLimit)||5); setEditing(false); }}>Save</Btn>
              </div>
            ) : (
              <div style={{ display:'flex', gap:8, alignItems:'center' }}>
                <div style={{ fontSize:32, fontWeight:900, color:spendColor }}>${DAILY_SPEND.toFixed(2)}</div>
                <div style={{ fontSize:14, color:'#7C6FAB' }}>/ ${dailyLimit.toFixed(2)}</div>
                <button onClick={()=>setEditing(true)} style={{ background:'#EDE9FE', border:'none', borderRadius:8, padding:'5px 10px', cursor:'pointer', fontFamily:'Nunito,sans-serif', fontSize:11, fontWeight:700, color:'#7C3AED' }}>Edit</button>
              </div>
            )}
          </div>
        </div>
        <div style={{ height:12, background:'rgba(255,255,255,0.5)', borderRadius:99, overflow:'hidden', marginBottom:8 }}>
          <div style={{ height:'100%', width:`${spendPct}%`, background:spendColor, borderRadius:99, transition:'width .5s' }}/>
        </div>
        <div style={{ display:'flex', justifyContent:'space-between', fontSize:12, fontWeight:700 }}>
          <span style={{ color:spendColor }}>{spendPct}% used today</span>
          <span style={{ color:'#7C6FAB' }}>${(dailyLimit-DAILY_SPEND).toFixed(2)} remaining</span>
        </div>
        {spendPct >= 90 && (
          <div style={{ marginTop:10, padding:'8px 12px', background:'#FEE2E2', borderRadius:8, fontSize:12, color:'#991B1B', fontWeight:700 }}>
            ⚠️ Budget nearly exhausted — further complex queries will route to Ollama or return degraded response
          </div>
        )}
      </Card>

      <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:20, marginBottom:20 }}>
        {/* Provider breakdown */}
        <Card style={{ padding:22 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Provider Breakdown · AgentRoutingService</div>
          {PROVIDER_BREAKDOWN.map(p=>(
            <div key={p.provider} style={{ marginBottom:14 }}>
              <div style={{ display:'flex', justifyContent:'space-between', marginBottom:4 }}>
                <span style={{ fontSize:12, fontWeight:700, color:'#1E1033' }}>{p.provider}</span>
                <div style={{ display:'flex', gap:10 }}>
                  <span style={{ fontSize:11, color:'#7C6FAB' }}>{p.queries} queries</span>
                  <span style={{ fontSize:12, fontWeight:900, color:p.color }}>${p.cost.toFixed(2)}</span>
                </div>
              </div>
              {p.tokens > 0 && (
                <div style={{ fontSize:10, color:'#9CA3AF', marginBottom:4 }}>{p.tokens.toLocaleString()} tokens</div>
              )}
              {p.cost > 0 && (
                <div style={{ height:6, background:'#EDE9FE', borderRadius:99 }}>
                  <div style={{ height:'100%', width:`${p.pct}%`, background:p.color, borderRadius:99 }}/>
                </div>
              )}
            </div>
          ))}
          <div style={{ padding:'10px 12px', background:'#F5F3FF', borderRadius:10, fontSize:12, color:'#7C3AED', fontWeight:700, marginTop:8 }}>
            💡 Simple queries → Ollama (free) · Complex → Bedrock (charged)
          </div>
        </Card>

        {/* Weekly spend chart */}
        <Card style={{ padding:22 }}>
          <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Weekly Spend · HistoricalTrackingService</div>
          <div style={{ display:'flex', gap:6, alignItems:'flex-end', height:80, marginBottom:10 }}>
            {WEEKLY.map(d=>{
              const h = Math.round((d.spend/maxSpend)*72);
              const isToday = d.day==='Sun';
              return (
                <div key={d.day} style={{ flex:1, display:'flex', flexDirection:'column', alignItems:'center', gap:4 }}>
                  <div style={{ fontSize:9, fontWeight:800, color:isToday?'#7C3AED':'#9CA3AF' }}>${d.spend.toFixed(2)}</div>
                  <div style={{ width:'100%', height:`${h}px`, borderRadius:'4px 4px 0 0', background:isToday?'linear-gradient(180deg,#E879A0,#7C3AED)':'#EDE9FE' }}/>
                  <div style={{ fontSize:10, fontWeight:700, color:isToday?'#7C3AED':'#9CA3AF' }}>{d.day}</div>
                </div>
              );
            })}
          </div>
          <div style={{ display:'flex', justifyContent:'space-between', fontSize:12 }}>
            <span style={{ color:'#7C6FAB' }}>7-day total: <strong style={{color:'#1E1033'}}>${WEEKLY.reduce((s,d)=>s+d.spend,0).toFixed(2)}</strong></span>
            <span style={{ color:'#7C6FAB' }}>Avg/day: <strong style={{color:'#1E1033'}}>${(WEEKLY.reduce((s,d)=>s+d.spend,0)/7).toFixed(2)}</strong></span>
          </div>
        </Card>
      </div>

      {/* Routing log */}
      <Card style={{ padding:22 }}>
        <div style={{ fontSize:14, fontWeight:800, color:'#1E1033', marginBottom:14 }}>Query Routing Log · Chain of Thought</div>
        {ROUTING_LOG.map((log,i)=>(
          <div key={i} style={{ display:'flex', gap:12, alignItems:'flex-start', padding:'10px 14px', background:'#F9F5FF', borderRadius:10, marginBottom:6, border:'1px solid #EDE9FE' }}>
            <div style={{ width:64, flexShrink:0, textAlign:'center' }}>
              <span style={{ fontSize:11, fontWeight:800, color:log.route==='Bedrock'?'#7C3AED':'#10B981', background:log.route==='Bedrock'?'#EDE9FE':'#D1FAE5', borderRadius:6, padding:'2px 7px' }}>{log.route==='Bedrock'?'☁️ Bedrock':'💻 Ollama'}</span>
            </div>
            <div style={{ flex:1 }}>
              <div style={{ fontSize:12, fontWeight:700, color:'#1E1033', marginBottom:2 }}>"{log.query}"</div>
              <div style={{ fontSize:10, color:'#7C6FAB' }}>Routed because: {log.reason}</div>
            </div>
            <div style={{ textAlign:'right', flexShrink:0 }}>
              {log.tokens > 0 && <div style={{ fontSize:11, color:'#7C6FAB' }}>{log.tokens.toLocaleString()} tokens</div>}
              <div style={{ fontSize:12, fontWeight:800, color:log.cost>0?'#EF4444':'#10B981' }}>{log.cost>0?`$${log.cost.toFixed(3)}`:'Free'}</div>
              <div style={{ fontSize:10, color:'#9CA3AF' }}>{log.time}</div>
            </div>
          </div>
        ))}
      </Card>
    </div>
  );
}

Object.assign(window, { Performance, ExternalSync, AIBudget });
