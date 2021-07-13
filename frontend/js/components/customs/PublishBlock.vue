<template>
  <div class="input" :class="textfieldClasses" v-show="isCurrentLocale" :hidden="!isCurrentLocale ?  true : null">
    <label class="input__label" :for="labelFor || name">
      Publication date <span class="input__required" v-if="required">*</span>
      <span class="input__lang" v-if="currentLocale && languages.length > 1" @click="updateLocale" data-tooltip-title="Switch language" v-tooltip>
        {{ currentLocale.value }}
      </span>
    </label>
    <div class="block-date">
      <a17-datepicker :key="`date_${currentLocale.value}`" :time_24hr="true" :initialValue="publication" name="publish_date" place-holder="Publication date" :enableTime="true" :allowInput="true" :staticMode="false" @input="updatePublicationDate" :clear="true"></a17-datepicker>
    </div>
    <span v-if="error && errorMessage" class="input__errorMessage f--small" v-html="errorMessage"></span>
    <span v-if="otherLocalesError" class="input__errorMessage f--small">{{ errorMessageLocales }}</span>
  </div>
</template>

<script>
  import { CONTENT, LANGUAGE } from '@/store/mutations'

  import InputMixin from '@/mixins/input'
  import InputframeMixin from '@/mixins/inputFrame'
  import LocaleMixin from '@/mixins/locale'

  export default {
    name: 'A17InputFrame',
    mixins: [LocaleMixin, InputMixin, InputframeMixin],
    computed: {
      textfieldClasses: function () {
        return {
          'input--error': this.error,
          'input--small': this.size === 'small'
        }
      },
      publication: function () {
        if (this.$parent.block.publication.content) {
          return this.$parent.block.publication.content[this.currentLocale.value]
        } else {
          return null
        }
      }
    },
    methods: {
      updatePublicationDate (date) {
        this.$store.commit(CONTENT.UPDATE_PUBLICATION_BLOCK, {
          block: this.$parent.block,
          index: this.$parent.index,
          date: date,
          lang: this.currentLocale.value
        })
      },
      updateLocale () {
        this.$store.commit(LANGUAGE.SWITCH_LANG, { oldValue: this.currentLocale })
        this.$emit('localize', this.currentLocale)
      }
    }
  }
</script>

<style lang="scss" scoped>
.block-date {
      margin-bottom: 5em;
      .datePicker  {
        margin: 0;
      }
    }

  .input {
    margin-top:35px;
    position: relative;
  }

  .input__add {
    position:absolute;
    top:0;
    right:0;
    text-decoration:none;
    color:$color__link;
  }

  .input__label {
    display:block;
    color:$color__text;
    margin-bottom:10px;
    position:relative;
  }

  .input__note {
    color:$color__text--light;
    display:block;

    @include breakpoint('small+') {
      display:inline;
      right:0;
      top:1px;
      position:absolute;
    }
  }

  .input__required {
    color:$color__icons;
    padding-left:5px;
  }

  .input__lang {
    border-radius:2px;
    display:inline-block;
    height:15px;
    line-height:15px;
    font-size:10px;
    color:$color__background;
    text-transform:uppercase;
    background:$color__icons;
    padding:0 5px;
    position:relative;
    top:-2px;
    margin-left:5px;
    cursor:pointer;
    user-select: none;
    letter-spacing:0;

    &:hover {
      background:$color__f--text;
    }
  }

  /* Input inline */
  .input__inliner {
    > .input {
      display:inline-block;
      margin-top:0;
      margin-right: 20px;

      .singleCheckbox {
        padding:7px 0 8px 0;
      }
    }
  }

  /* small variant */

  .input--small {
    margin-top:16px;

    .input__label {
      margin-bottom:9px;
      @include font-small;
    }
  }

  /* Error variant */
  .input--error {
    > label {
      color:$color__error;

      .input__lang {
        background-color:$color__error;
      }
    }

    .form__field,
    .select__input,
    .input__field,
    .v-select .dropdown-toggle {
      border-color:$color__error;

      &.s--focus,
      &:hover,
      &:focus {
        border-color:$color__error;
      }
    }
  }

  .input__errorMessage {
    color:$color__error;
    margin-top:10px;
    display:block;
  }
</style>
