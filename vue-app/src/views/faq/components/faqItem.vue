<template>
  <div
      :id="item.id"
      class="mgs-accordion-item mgs-animate-in"
      :class="{ 'mgs-open': isOpen }"
      :style="{ animationDelay: (item.delay ?? 0) + 's' }"
  >

    <div class="mgs-accordion-header" @click="$emit('toggle', item.id)">
      <span class="mgs-question-icon">
        <i class="mg-question-icon mgs-aq-ic"></i>
      </span>

      <div class="mgs-accordion-info">
        <h3>{{ item.title }}</h3>
        <p>{{ item.summary }}</p>
      </div>

      <span class="mgs-accordion-arrow">
        <i class="mg-arrow-icon mgs-aci-row-ic"></i>
      </span>
    </div>

    <div
        ref="contentRef"
        class="mgs-accordion-content"
        :style="{ maxHeight: isOpen ? contentHeight : '0px' }"
    >
      <div class="mgs-accordion-body">

        <div class="mgs-tutorial-text">
          <p>{{ item.content }}</p>

          <ul v-if="item.steps && item.steps.length > 0">
            <li v-for="(step, index) in item.steps" :key="index">
              <strong>{{ step.title }}:</strong>
              {{ step.text }}
            </li>
          </ul>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },

  isOpen: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle'])


const contentHeight = ref('0px')
const contentRef = ref(null)

watch(
    () => props.isOpen,
    async (opened) => {
      await nextTick()
      if (opened) {
        const body = contentRef.value
        if (body) {
          contentHeight.value = body.scrollHeight + 'px'
        }
      } else {
        contentHeight.value = '0px'
      }
    },
    { immediate: true }
)
</script>