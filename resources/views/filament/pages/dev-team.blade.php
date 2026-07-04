<x-filament-panels::page>
    @php
        $services = [
            ['💻', 'تطوير تطبيقات الويب بـ Laravel وPHP'],
            ['🗂️', 'لوحات التحكم والأنظمة الإدارية'],
            ['🎨', 'تصميم واجهات المستخدم UI/UX'],
            ['📱', 'تطوير تطبيقات الهاتف'],
            ['🧩', 'تحليل الأنظمة وقواعد البيانات'],
            ['🗄️', 'تصميم قواعد البيانات وتحسين أدائها'],
            ['🔗', 'التكامل مع واجهات البرمجة APIs'],
            ['☁️', 'حلول الحوسبة السحابية والاستضافة'],
            ['🔐', 'الأمن السيبراني وأمن التطبيقات'],
            ['⚡', 'تحسين الأداء وتجربة المستخدم'],
            ['📊', 'إدارة المشاريع التقنية'],
            ['🚀', 'الاستشارات التقنية والتحول الرقمي'],
        ];

        $team = [
            ['عمر محمد صالح', 'قائد الفريق · Team Leader', 'الإشراف العام على الفريق، وإدارة الرؤية التقنية، وضمان جودة تنفيذ المشاريع.', '🧭'],
            ['عثمان محمد أحمد كابتوت', 'مدير المشاريع · Project Manager', 'إدارة المشاريع والتخطيط وتوزيع المهام ومتابعة التنفيذ حتى التسليم.', '📋'],
            ['أبو بكر حسن محمد نصر', 'محلل الأنظمة وقواعد البيانات', 'تحليل المتطلبات، تصميم قواعد البيانات، تحسين البنية التقنية والتخطيط الفني.', '🧠'],
            ['ابوبكر الأمين أبو الحسن', 'مدير العلاقات العامة · PR Manager', 'التواصل مع العملاء وبناء العلاقات والتنسيق بين الفريق والجهات الخارجية.', '🤝'],
            ['أيمن محمد', 'مطوّر · Developer', 'تطوير واجهات وأنظمة الويب وبرمجة مكوّنات النظام وضمان جودتها.', '💻'],
        ];
    @endphp

    <style>
        .qd-hero {
            background: linear-gradient(135deg, rgb(var(--primary-500)) 0%, rgb(var(--primary-700)) 55%, rgb(var(--primary-900)) 100%);
            color: #fff; border-radius: 1.25rem; padding: 2.5rem 2rem; text-align: center;
            box-shadow: 0 18px 45px rgba(0,0,0,.22); position: relative; overflow: hidden;
        }
        .qd-hero h1 { font-size: 2.1rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
        .qd-hero .sub { font-size: 1.05rem; opacity: .92; margin-top: .4rem; }
        .qd-badges { display:flex; gap:.5rem; justify-content:center; flex-wrap:wrap; margin-top:1rem; }
        .qd-badge { background: rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.25); padding:.3rem .8rem; border-radius:99px; font-size:.8rem; }
        .qd-section { background: var(--fi-color-white,#fff); border:1px solid rgba(0,0,0,.06); border-radius:1rem; padding:1.5rem; margin-top:1.25rem; box-shadow:0 2px 10px rgba(0,0,0,.04); }
        .dark .qd-section { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); }
        .qd-title { display:flex; align-items:center; gap:.5rem; font-size:1.2rem; font-weight:800; margin:0 0 1rem; color: rgb(var(--primary-600)); }
        .qd-title .bar { width:.35rem; height:1.3rem; background: rgb(var(--primary-600)); border-radius:99px; }
        .qd-lead { line-height:2; color: var(--fi-color-gray-600,#475569); font-size:1.02rem; }
        .dark .qd-lead { color:#cbd5e1; }
        .qd-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(230px,1fr)); gap:.75rem; }
        .qd-chip { display:flex; align-items:center; gap:.6rem; padding:.7rem .9rem; border-radius:.7rem; background: rgba(99,102,241,.06); border:1px solid rgba(99,102,241,.12); font-weight:600; font-size:.92rem; }
        .dark .qd-chip { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
        .qd-chip .ic { font-size:1.25rem; }
        .qd-team { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap:1rem; }
        .qd-card { border:1px solid rgba(0,0,0,.07); border-radius:1rem; padding:1.25rem; text-align:center; transition:transform .2s, box-shadow .2s; background: var(--fi-color-white,#fff); }
        .dark .qd-card { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); }
        .qd-card:hover { transform: translateY(-4px); box-shadow:0 12px 28px rgba(0,0,0,.12); }
        .qd-avatar { width:64px; height:64px; margin:0 auto .75rem; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.8rem; background: linear-gradient(135deg, rgb(var(--primary-500)), rgb(var(--primary-700))); box-shadow:0 6px 16px rgba(0,0,0,.18); }
        .qd-card .name { font-weight:800; font-size:1.05rem; }
        .qd-role { display:inline-block; margin:.4rem 0; font-size:.78rem; font-weight:700; color: rgb(var(--primary-600)); background: rgba(99,102,241,.1); padding:.2rem .7rem; border-radius:99px; }
        .qd-card .desc { color: var(--fi-color-gray-500,#64748b); font-size:.85rem; line-height:1.7; }
        .dark .qd-card .desc { color:#94a3b8; }
        .qd-quote { border-{{ app()->getLocale()==='ar' ? 'right' : 'left' }}: 4px solid rgb(var(--primary-600)); padding:.5rem 1rem; font-size:1.05rem; line-height:2; color: var(--fi-color-gray-700,#334155); font-style: italic; }
        .dark .qd-quote { color:#cbd5e1; }
        .qd-footer { text-align:center; margin-top:1.5rem; padding:1.25rem; border-radius:1rem; background: linear-gradient(135deg, rgb(var(--primary-700)), rgb(var(--primary-900))); color:#fff; }
        .qd-footer .brand { font-weight:800; font-size:1.1rem; }
        .qd-footer .small { opacity:.85; font-size:.8rem; margin-top:.35rem; line-height:1.8; }
    </style>

    {{-- Hero --}}
    <div class="qd-hero">
        <div style="background:#fff;border-radius:1rem;padding:1rem 1.5rem;display:inline-block;box-shadow:0 8px 24px rgba(0,0,0,.18);margin-bottom:1rem;">
            <img src="{{ asset('images/quantum-logo.png') }}" alt="Quantum Dev Team"
                 style="max-height:140px;max-width:100%;display:block;"
                 onerror="this.style.display='none';document.getElementById('qd-fallback').style.display='block';">
            <div id="qd-fallback" style="display:none;">
                <div style="font-size:2.6rem;color:rgb(var(--primary-700));">⚛️</div>
                <div style="font-size:1.6rem;font-weight:800;color:rgb(var(--primary-700));">Quantum Dev Team</div>
            </div>
        </div>
        <div class="sub">فريق كوانتم للتطوير البرمجي — CODE BEYOND BOUNDARIES</div>
        <div class="qd-badges">
            <span class="qd-badge">عمل حر · Freelance</span>
            <span class="qd-badge">Laravel · PHP</span>
            <span class="qd-badge">UI/UX</span>
            <span class="qd-badge">جودة عالية</span>
        </div>
    </div>

    {{-- نبذة --}}
    <div class="qd-section">
        <h2 class="qd-title"><span class="bar"></span> نبذة عن الفريق</h2>
        <p class="qd-lead">
            <strong>Quantum Dev Team</strong> فريق تطوير تقني مستقل يعمل بأسلوب العمل الحر، ويضم مجموعة من المطورين والمتخصصين من مناطق مختلفة، يجمعهم هدف واحد: تقديم حلول تقنية احترافية بمعايير عالية الجودة.
            نعمل بروح الفريق الواحد، ونحوّل الأفكار إلى أنظمة ومنصات رقمية عملية وقابلة للتطوير، مع الالتزام بأفضل ممارسات هندسة البرمجيات والأداء والأمان وتجربة المستخدم.
        </p>
    </div>

    {{-- مجالات العمل --}}
    <div class="qd-section">
        <h2 class="qd-title"><span class="bar"></span> مجالات عمل الفريق</h2>
        <div class="qd-grid">
            @foreach ($services as [$icon, $label])
                <div class="qd-chip"><span class="ic">{{ $icon }}</span> {{ $label }}</div>
            @endforeach
        </div>
    </div>

    {{-- فريق الإدارة --}}
    <div class="qd-section">
        <h2 class="qd-title"><span class="bar"></span> فريق الإدارة</h2>
        <div class="qd-team">
            @foreach ($team as [$name, $role, $desc, $emoji])
                <div class="qd-card">
                    <div class="qd-avatar">{{ $emoji }}</div>
                    <div class="name">{{ $name }}</div>
                    <div class="qd-role">{{ $role }}</div>
                    <div class="desc">{{ $desc }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- رسالة الفريق --}}
    <div class="qd-section">
        <h2 class="qd-title"><span class="bar"></span> رسالة الفريق</h2>
        <div class="qd-quote">
            نؤمن بأن نجاح أي مشروع يبدأ بفهم احتياجات العميل، ثم تحويلها إلى حلول تقنية عملية ومستقرة وقابلة للتوسع، مع المحافظة على أعلى معايير الجودة والاحترافية.
        </div>
    </div>

    {{-- الحقوق --}}
    <div class="qd-footer">
        <div class="brand">© Quantum Dev Team — جميع الحقوق محفوظة</div>
        <div class="small">
            جميع حقوق التصميم والتطوير والبرمجة والملفات المصدرية محفوظة لصالح Quantum Dev Team.
            يُمنع إعادة استخدام أو توزيع أو نسخ أي جزء من النظام إلا بموافقة الفريق.
        </div>
    </div>
</x-filament-panels::page>
