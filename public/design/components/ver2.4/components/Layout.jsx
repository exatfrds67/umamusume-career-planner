
const AppContext = React.createContext({});

const NAV_ITEMS = [
  { id:'dashboard',      label:'Dashboard',      icon:'home',     section:'main' },
  { id:'characters',     label:'Characters',     icon:'users',    section:'main' },
  { id:'training',       label:'Training',       icon:'bolt',     section:'main' },
  { id:'races',          label:'Races',          icon:'trophy',   section:'main' },
  { id:'skills',         label:'Skills',         icon:'sparkles', section:'main' },
  { id:'support-cards',  label:'Support Cards',  icon:'squares',  section:'main' },
  { id:'ai-advisor',     label:'AI Advisor',     icon:'cpu',      section:'main' },
  { id:'achievements',   label:'Achievements',   icon:'trophy',   section:'main' },
  { id:'mcp',            label:'MCP Monitor',    icon:'signal',   section:'system' },
  { id:'performance',    label:'Performance',    icon:'chart',    section:'system' },
  { id:'admin',          label:'Admin Panel',    icon:'shield',   section:'system' },
  { id:'ocr',            label:'OCR Upload',     icon:'camera',   section:'data' },
  { id:'data',           label:'Data Management',icon:'database', section:'data' },
  { id:'settings',       label:'Settings',       icon:'cog',      section:'data' },
];

function Sidebar({ screen, setScreen, collapsed, setCollapsed }) {
  const char = MOCK_CHARACTERS[0];

  const NavItem = ({ item }) => {
    const active = screen === item.id;
    return (
      <button
        onClick={() => setScreen(item.id)}
        style={{
          display:'flex', alignItems:'center', gap:12,
          padding: collapsed ? '10px 0' : '10px 14px',
          justifyContent: collapsed ? 'center' : 'flex-start',
          width:'100%', border:'none', cursor:'pointer', borderRadius:10,
          background: active ? 'rgba(232,121,160,0.18)' : 'transparent',
          borderLeft: active ? '3px solid #E879A0' : '3px solid transparent',
          color: active ? '#F9A8D4' : 'rgba(255,255,255,0.55)',
          fontFamily:'Nunito,sans-serif', fontSize:13.5, fontWeight: active ? 700 : 500,
          transition:'all .15s', marginBottom:2, position:'relative',
        }}
        title={collapsed ? item.label : ''}
      >
        <Icon name={item.icon} size={18} color={active ? '#F9A8D4' : 'rgba(255,255,255,0.5)'} />
        {!collapsed && <span>{item.label}</span>}
        {item.id === 'ai-advisor' && !collapsed && (
          <span style={{ marginLeft:'auto', background:'#E879A0', color:'#fff', borderRadius:20, padding:'1px 7px', fontSize:10, fontWeight:800 }}>AI</span>
        )}
      </button>
    );
  };

  const SectionLabel = ({ label }) => collapsed ? null : (
    <div style={{ fontSize:10, fontWeight:800, letterSpacing:1.5, color:'rgba(255,255,255,0.3)', textTransform:'uppercase', padding:'16px 14px 6px', marginTop:4 }}>{label}</div>
  );

  const storageMode = 'Account';

  return (
    <div style={{
      width: collapsed ? 64 : 240, minHeight:'100vh', flexShrink:0,
      background:'linear-gradient(180deg,#150D35 0%,#0A0620 100%)',
      display:'flex', flexDirection:'column', transition:'width .2s ease',
      borderRight:'1px solid rgba(255,255,255,0.06)',
      position:'relative', zIndex:10,
    }}>
      {/* Logo */}
      <div style={{ padding: collapsed ? '20px 0' : '20px 16px', borderBottom:'1px solid rgba(255,255,255,0.07)', display:'flex', alignItems:'center', gap:10, justifyContent: collapsed ? 'center' : 'flex-start' }}>
        <div style={{ width:36, height:36, borderRadius:10, background:'linear-gradient(135deg,#E879A0,#7C3AED)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:18, flexShrink:0 }}>🏇</div>
        {!collapsed && (
          <div>
            <div style={{ fontSize:14, fontWeight:900, background:'linear-gradient(135deg,#F9A8D4,#C4B5FD)', WebkitBackgroundClip:'text', WebkitTextFillColor:'transparent', lineHeight:1.1 }}>Uma Planner</div>
            <div style={{ fontSize:10, color:'rgba(255,255,255,0.35)', fontWeight:600 }}>Career Planner v2.4</div>
          </div>
        )}
      </div>

      {/* Run selector */}
      {!collapsed && (
        <div style={{ margin:'12px 10px', background:'rgba(255,255,255,0.07)', borderRadius:10, padding:'9px 12px', cursor:'pointer', border:'1px solid rgba(255,255,255,0.1)' }}>
          <div style={{ fontSize:10, color:'rgba(255,255,255,0.4)', fontWeight:700, marginBottom:2 }}>ACTIVE RUN</div>
          <div style={{ display:'flex', alignItems:'center', justifyContent:'space-between' }}>
            <div>
              <div style={{ fontSize:13, fontWeight:800, color:'#F9A8D4' }}>{char.name}</div>
              <div style={{ fontSize:11, color:'rgba(255,255,255,0.45)' }}>Turn {char.turn} · {char.stage}</div>
            </div>
            <Icon name='chevD' size={14} color='rgba(255,255,255,0.4)' />
          </div>
        </div>
      )}

      {/* Nav */}
      <nav style={{ flex:1, padding: collapsed ? '8px 8px' : '8px 8px', overflowY:'auto' }}>
        <SectionLabel label="Main" />
        {NAV_ITEMS.filter(i=>i.section==='main').map(i=><NavItem key={i.id} item={i}/>)}
        <SectionLabel label="System" />
        {NAV_ITEMS.filter(i=>i.section==='system').map(i=><NavItem key={i.id} item={i}/>)}
        <SectionLabel label="Data" />
        {NAV_ITEMS.filter(i=>i.section==='data').map(i=><NavItem key={i.id} item={i}/>)}
      </nav>

      {/* Bottom */}
      <div style={{ padding: collapsed ? '12px 8px' : '12px 10px', borderTop:'1px solid rgba(255,255,255,0.07)' }}>
        {!collapsed && (
          <div style={{ display:'flex', alignItems:'center', gap:8, padding:'8px 6px', borderRadius:10, background:'rgba(255,255,255,0.05)', marginBottom:8 }}>
            <div style={{ width:8, height:8, borderRadius:99, background: storageMode==='Account' ? '#10B981' : '#F59E0B', flexShrink:0 }}/>
            <div style={{ flex:1 }}>
              <div style={{ fontSize:10, color:'rgba(255,255,255,0.35)', fontWeight:700 }}>STORAGE</div>
              <div style={{ fontSize:12, color:'rgba(255,255,255,0.7)', fontWeight:700 }}>{storageMode} Mode</div>
            </div>
          </div>
        )}
        <button
          onClick={() => setCollapsed(!collapsed)}
          style={{ width:'100%', background:'rgba(255,255,255,0.07)', border:'none', borderRadius:8, padding:'8px', cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', color:'rgba(255,255,255,0.4)', transition:'all .15s' }}
        >
          <Icon name={collapsed ? 'chevR' : 'chevL'} size={16} color='rgba(255,255,255,0.4)' />
        </button>
      </div>
    </div>
  );
}

function Header({ screen, setScreen }) {
  const char = MOCK_CHARACTERS[0];
  const label = NAV_ITEMS.find(i=>i.id===screen)?.label || 'Dashboard';
  const [notifOpen, setNotifOpen] = React.useState(false);

  const stageColor = char.stage === 'Junior Year' ? '#3B82F6' : char.stage === 'Classic Year' ? '#7C3AED' : '#E879A0';

  return (
    <div style={{
      height:64, background:'rgba(255,255,255,0.95)', backdropFilter:'blur(12px)',
      borderBottom:'1px solid #EDE9FE', display:'flex', alignItems:'center',
      padding:'0 24px', gap:16, position:'sticky', top:0, zIndex:5,
      boxShadow:'0 1px 8px rgba(124,58,237,0.06)',
    }}>
      <div style={{ flex:1 }}>
        <div style={{ fontSize:18, fontWeight:900, color:'#1E1033' }}>{label}</div>
        <div style={{ fontSize:12, color:'#7C6FAB', display:'flex', alignItems:'center', gap:6 }}>
          <span style={{ color:stageColor, fontWeight:700 }}>{char.stage}</span>
          <span>·</span>
          <span>Turn {char.turn} / {char.totalTurns}</span>
          <span>·</span>
          <span style={{ color:'#10B981', fontWeight:700 }}>{char.scenario}</span>
        </div>
      </div>

      {/* Energy pill */}
      <div style={{ display:'flex', alignItems:'center', gap:6, background:'#F9F5FF', borderRadius:20, padding:'6px 12px', border:'1px solid #EDE9FE' }}>
        <span style={{ fontSize:12 }}>⚡</span>
        <div style={{ width:60, height:6, background:'#EDE9FE', borderRadius:99 }}>
          <div style={{ height:'100%', width:`${char.energy}%`, background:'linear-gradient(90deg,#E879A0,#7C3AED)', borderRadius:99 }}/>
        </div>
        <span style={{ fontSize:12, fontWeight:700, color:'#7C3AED' }}>{char.energy}%</span>
      </div>

      <MoodChip mood={char.mood} />

      {/* Notif bell */}
      <div style={{ position:'relative' }}>
        <button onClick={()=>setNotifOpen(!notifOpen)} style={{ background:'#F9F5FF', border:'1px solid #EDE9FE', borderRadius:10, padding:'8px', cursor:'pointer', position:'relative' }}>
          <Icon name='bell' size={18} color='#7C6FAB' />
          <span style={{ position:'absolute', top:5, right:5, width:8, height:8, background:'#E879A0', borderRadius:99, border:'2px solid #fff' }}/>
        </button>
        {notifOpen && (
          <div style={{ position:'absolute', top:44, right:0, width:280, background:'#fff', borderRadius:12, border:'1px solid #EDE9FE', boxShadow:'0 8px 32px rgba(124,58,237,0.15)', zIndex:50, overflow:'hidden' }}>
            <div style={{ padding:'12px 16px', borderBottom:'1px solid #EDE9FE', fontWeight:800, fontSize:13, color:'#1E1033' }}>Notifications</div>
            {[
              { icon:'⚠️', text:'Stamina goal at risk — 5 turns behind', time:'2m ago', color:'#FEF3C7' },
              { icon:'🏆', text:'Mile Cup in 7 turns — readiness 85%', time:'1h ago', color:'#EDE9FE' },
              { icon:'💡', text:'New skill hint: Power Surge available', time:'3h ago', color:'#F0FDF4' },
            ].map((n,i)=>(
              <div key={i} style={{ padding:'10px 16px', background:n.color, borderBottom:'1px solid #EDE9FE', cursor:'pointer' }}>
                <div style={{ fontSize:13, fontWeight:600, color:'#1E1033', display:'flex', gap:8 }}>
                  <span>{n.icon}</span><span>{n.text}</span>
                </div>
                <div style={{ fontSize:11, color:'#7C6FAB', marginTop:2 }}>{n.time}</div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Avatar */}
      <div style={{ width:36, height:36, borderRadius:99, background:'linear-gradient(135deg,#E879A0,#7C3AED)', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16, cursor:'pointer', fontWeight:900, color:'#fff' }}>T</div>
    </div>
  );
}

function Layout({ children, screen, setScreen }) {
  const [collapsed, setCollapsed] = React.useState(false);

  return (
    <AppContext.Provider value={{ screen, setScreen }}>
      <div style={{ display:'flex', minHeight:'100vh', background:'#F9F5FF', fontFamily:'Nunito,sans-serif' }}>
        <Sidebar screen={screen} setScreen={setScreen} collapsed={collapsed} setCollapsed={setCollapsed} />
        <div style={{ flex:1, display:'flex', flexDirection:'column', minWidth:0 }}>
          <Header screen={screen} setScreen={setScreen} />
          <main style={{ flex:1, padding:'24px', overflowY:'auto' }}>
            {children}
          </main>
        </div>
      </div>
    </AppContext.Provider>
  );
}

Object.assign(window, { Layout, AppContext });
