<script setup lang="ts">
import { computed, ref, useId } from 'vue'
import type { SeriesPoint } from '@/api'
import { formatDate, formatNumber } from '@/lib/date'

const props = defineProps<{
  points: SeriesPoint[]
  unit?: string
}>()

const width = 860
const height = 320
const padding = { top: 24, right: 18, bottom: 38, left: 52 }

const gradientId = useId()
const svgRef = ref<SVGSVGElement | null>(null)
const activeIndex = ref<number | null>(null)

const innerWidth = width - padding.left - padding.right
const innerHeight = height - padding.top - padding.bottom

const maxValue = computed(() => {
  const values = props.points.map((point) => point.mean_value)
  const raw = Math.max(...values, 1) * 1.12
  const magnitude = 10 ** Math.floor(Math.log10(raw))
  const normalized = raw / magnitude
  const nice = normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10

  return nice * magnitude
})

function xAt(index: number): number {
  if (props.points.length <= 1) {
    return padding.left + innerWidth / 2
  }

  return padding.left + (index / (props.points.length - 1)) * innerWidth
}

function yAt(value: number): number {
  return padding.top + (1 - value / maxValue.value) * innerHeight
}

const linePath = computed(() =>
  props.points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${xAt(index)} ${yAt(point.mean_value)}`).join(' '),
)

const areaPath = computed(() => {
  if (props.points.length === 0) {
    return ''
  }

  const baseline = padding.top + innerHeight
  return `${linePath.value} L ${xAt(props.points.length - 1)} ${baseline} L ${xAt(0)} ${baseline} Z`
})

const yTicks = computed(() =>
  [0, 0.25, 0.5, 0.75, 1].map((fraction) => {
    const value = maxValue.value * (1 - fraction)
    return { value, y: padding.top + fraction * innerHeight }
  }),
)

const xTicks = computed(() => {
  const count = props.points.length
  const tickCount = Math.min(6, count)

  if (count === 0) {
    return []
  }

  if (count <= tickCount) {
    return props.points.map((point, index) => ({
      index,
      x: xAt(index),
      label: formatDate(point.date, { day: 'numeric', month: 'short' }),
    }))
  }

  const indices = new Set<number>()
  for (let i = 0; i < tickCount; i++) {
    indices.add(Math.round((i / (tickCount - 1)) * (count - 1)))
  }

  return [...indices].map((index) => {
    const point = props.points[index]
    return {
      index,
      x: xAt(index),
      label: point ? formatDate(point.date, { day: 'numeric', month: 'short' }) : '',
    }
  })
})

const activePoint = computed(() => {
  if (activeIndex.value === null) {
    return null
  }

  const point = props.points[activeIndex.value]
  if (!point) {
    return null
  }

  return {
    point,
    x: xAt(activeIndex.value),
    y: yAt(point.mean_value),
  }
})

function updateActiveFromPointer(event: PointerEvent): void {
  const svg = svgRef.value
  const length = props.points.length

  if (!svg || length === 0) {
    return
  }

  const rect = svg.getBoundingClientRect()
  const px = ((event.clientX - rect.left) / rect.width) * width
  const ratio = Math.min(1, Math.max(0, (px - padding.left) / innerWidth))
  const index = length === 1 ? 0 : Math.round(ratio * (length - 1))

  activeIndex.value = Math.min(length - 1, Math.max(0, index))
}

function onKeydown(event: KeyboardEvent): void {
  const length = props.points.length

  if (length === 0) {
    return
  }

  const current = activeIndex.value ?? length - 1

  if (event.key === 'ArrowLeft') {
    event.preventDefault()
    activeIndex.value = Math.max(0, current - 1)
  } else if (event.key === 'ArrowRight') {
    event.preventDefault()
    activeIndex.value = Math.min(length - 1, current + 1)
  } else if (event.key === 'Escape') {
    activeIndex.value = null
  }
}

const tooltipStyle = computed(() => {
  if (!activePoint.value) {
    return {}
  }

  return {
    left: `${(activePoint.value.x / width) * 100}%`,
    top: `${(activePoint.value.y / height) * 100}%`,
  }
})
</script>

<template>
  <div class="chart">
    <div class="chart__canvas">
      <svg
        ref="svgRef"
        :viewBox="`0 0 ${width} ${height}`"
        role="img"
        aria-label="Daily average PM2.5 concentration chart"
        tabindex="0"
        @pointermove="updateActiveFromPointer"
        @pointerleave="activeIndex = null"
        @keydown="onKeydown"
        @blur="activeIndex = null"
      >
        <defs>
          <linearGradient :id="gradientId" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stop-color="var(--brand)" stop-opacity="0.28" />
            <stop offset="100%" stop-color="var(--brand)" stop-opacity="0" />
          </linearGradient>
        </defs>

        <g v-for="tick in yTicks" :key="tick.y">
          <line
            :x1="padding.left"
            :x2="width - padding.right"
            :y1="tick.y"
            :y2="tick.y"
            class="chart__grid"
          />
          <text :x="padding.left - 10" :y="tick.y + 4" class="chart__axis-label" text-anchor="end">
            {{ formatNumber(tick.value, 0) }}
          </text>
        </g>

        <path :d="areaPath" :fill="`url(#${gradientId})`" />
        <path :d="linePath" class="chart__line" />

        <g v-for="tick in xTicks" :key="`x-${tick.index}`">
          <text :x="tick.x" :y="height - 14" class="chart__axis-label" text-anchor="middle">
            {{ tick.label }}
          </text>
        </g>

        <text
          :x="padding.left - 38"
          :y="padding.top - 8"
          class="chart__axis-title"
          text-anchor="start"
        >
          {{ unit ?? 'µg/m³' }}
        </text>

        <g v-if="activePoint">
          <line
            :x1="activePoint.x"
            :x2="activePoint.x"
            :y1="padding.top"
            :y2="padding.top + innerHeight"
            class="chart__crosshair"
          />
          <circle :cx="activePoint.x" :cy="activePoint.y" r="5" class="chart__dot" />
        </g>
      </svg>

      <div v-if="activePoint" class="chart__tooltip" :style="tooltipStyle">
        <strong>{{ formatNumber(activePoint.point.mean_value) }} µg/m³</strong>
        <span>{{ formatDate(activePoint.point.date) }}</span>
        <span>{{ activePoint.point.station_count }} station{{ activePoint.point.station_count === 1 ? '' : 's' }}</span>
      </div>
    </div>

    <p class="chart__hint">Hover, tap or use the arrow keys to inspect daily values.</p>
  </div>
</template>

<style scoped>
.chart {
  display: grid;
  gap: 8px;
}

.chart__canvas {
  position: relative;
}

svg {
  display: block;
  width: 100%;
  height: auto;
  overflow: visible;
  border-radius: 12px;
  outline: none;
}

svg:focus-visible {
  outline: 2px solid var(--brand);
  outline-offset: 4px;
}

.chart__grid {
  stroke: var(--border-soft);
  stroke-width: 1;
}

.chart__axis-label {
  fill: var(--muted);
  font-size: 10px;
  font-variant-numeric: tabular-nums;
}

.chart__axis-title {
  fill: var(--muted);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.06em;
}

.chart__line {
  fill: none;
  stroke: var(--brand-strong);
  stroke-width: 2.5;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.chart__crosshair {
  stroke: var(--brand);
  stroke-dasharray: 4 4;
  stroke-width: 1;
}

.chart__dot {
  fill: var(--surface);
  stroke: var(--brand-strong);
  stroke-width: 3;
}

.chart__tooltip {
  position: absolute;
  z-index: 5;
  display: grid;
  gap: 1px;
  padding: 8px 11px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: var(--surface);
  box-shadow: var(--shadow-md);
  color: var(--ink);
  font-size: 11px;
  pointer-events: none;
  transform: translate(-50%, -125%);
  white-space: nowrap;
}

.chart__tooltip strong {
  font-size: 13px;
}

.chart__tooltip span {
  color: var(--muted);
}

.chart__hint {
  margin: 0;
  color: var(--muted);
  font-size: 11px;
  text-align: right;
}
</style>
