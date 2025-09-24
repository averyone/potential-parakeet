<template>
  <div class="editable-overlay">
    <div 
      v-for="field in pageFields" 
      :key="field.id"
      :class="['editable-field', { selected: selectedFieldId === field.id }]"
      :style="getFieldStyle(field)"
      @click="selectField(field)"
    >
      <component 
        :is="getFieldComponent(field.type)"
        :field="field"
        :value="field.value || ''"
        @input="updateField(field, $event)"
        @focus="selectField(field)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditableOverlay',
  props: {
    fields: {
      type: Array,
      default: () => []
    },
    page: {
      type: Number,
      default: 0
    },
    zoom: {
      type: Number,
      default: 1
    }
  },
  
  data() {
    return {
      selectedFieldId: null
    }
  },
  
  computed: {
    pageFields() {
      // Filter fields for current page
      return this.fields.filter(field => 
        field.coordinates && field.coordinates.page === (this.page + 1)
      )
    }
  },
  
  methods: {
    getFieldStyle(field) {
      if (!field.coordinates) {
        return {}
      }
      
      const coords = field.coordinates
      const zoom = this.zoom
      
      return {
        left: `${coords.x * zoom}px`,
        top: `${coords.y * zoom}px`,
        width: `${coords.width * zoom}px`,
        height: `${coords.height * zoom}px`
      }
    },
    
    getFieldComponent(fieldType) {
      switch (fieldType?.toLowerCase()) {
        case 'text':
        case 'textfield':
          return 'FieldText'
        case 'textarea':
          return 'FieldTextarea'
        case 'choice':
        case 'listbox':
        case 'combobox':
          return 'FieldSelect'
        case 'button':
        case 'checkbox':
          return 'FieldCheckbox'
        case 'radiobutton':
          return 'FieldRadio'
        default:
          return 'FieldText'
      }
    },
    
    selectField(field) {
      this.selectedFieldId = field.id
      this.$emit('field-selected', field)
    },
    
    updateField(field, event) {
      const value = event.target ? event.target.value : event
      field.value = value
      this.$emit('field-updated', field, value)
    }
  },
  
  components: {
    FieldText: {
      template: `
        <input 
          type="text" 
          class="field-input"
          :value="value"
          @input="$emit('input', $event)"
          @focus="$emit('focus')"
          :placeholder="field.name"
        />
      `,
      props: ['field', 'value']
    },
    
    FieldTextarea: {
      template: `
        <textarea 
          class="field-input"
          :value="value"
          @input="$emit('input', $event)"
          @focus="$emit('focus')"
          :placeholder="field.name"
        ></textarea>
      `,
      props: ['field', 'value']
    },
    
    FieldSelect: {
      template: `
        <select 
          class="field-input"
          :value="value"
          @change="$emit('input', $event)"
          @focus="$emit('focus')"
        >
          <option value="">Select...</option>
          <option 
            v-for="option in field.options" 
            :key="option" 
            :value="option"
          >
            {{ option }}
          </option>
        </select>
      `,
      props: ['field', 'value']
    },
    
    FieldCheckbox: {
      template: `
        <label class="field-input checkbox-label">
          <input 
            type="checkbox" 
            :checked="isChecked"
            @change="handleChange"
            @focus="$emit('focus')"
          />
          <span class="checkbox-text">{{ field.name }}</span>
        </label>
      `,
      props: ['field', 'value'],
      computed: {
        isChecked() {
          return this.value === 'Yes' || this.value === 'true' || this.value === '1' || this.value === 'On'
        }
      },
      methods: {
        handleChange(event) {
          const value = event.target.checked ? 'Yes' : 'Off'
          this.$emit('input', { target: { value } })
        }
      }
    },
    
    FieldRadio: {
      template: `
        <div class="field-input radio-group">
          <label 
            v-for="option in field.options" 
            :key="option"
            class="radio-label"
          >
            <input 
              type="radio" 
              :name="field.name"
              :value="option"
              :checked="value === option"
              @change="$emit('input', $event)"
              @focus="$emit('focus')"
            />
            <span>{{ option }}</span>
          </label>
        </div>
      `,
      props: ['field', 'value']
    }
  }
}
</script>

<style scoped>
.editable-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.editable-field {
  position: absolute;
  border: 2px dashed #007bff;
  background: rgba(0, 123, 255, 0.1);
  cursor: pointer;
  pointer-events: auto;
  transition: all 0.2s;
}

.editable-field:hover {
  border-color: #0056b3;
  background: rgba(0, 123, 255, 0.2);
}

.editable-field.selected {
  border-style: solid;
  border-width: 2px;
  border-color: #28a745;
  background: rgba(40, 167, 69, 0.1);
}

.field-input {
  width: 100%;
  height: 100%;
  border: none;
  background: transparent;
  padding: 2px 4px;
  font-size: 12px;
  resize: none;
}

.field-input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.9);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
}

.checkbox-text {
  font-size: 10px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 2px;
  cursor: pointer;
  font-size: 10px;
}

.radio-label input[type="radio"] {
  width: auto;
  height: auto;
}

select.field-input {
  font-size: 11px;
}

textarea.field-input {
  resize: none;
  font-family: inherit;
}
</style>