import Quill from 'quill'

Quill.debug('error')

// Quill fix imported from https://github.com/decidim/decidim/pull/7999/files
const Scroll = Quill.import("blots/scroll");
const Parchment = Quill.import("parchment");

// Override quill/blots/scroll.js
class ScrollOverride extends Scroll {
  optimize(mutations = [], context = {}) {
    if (this.batch === true) {
      return;
    }

    this.parchmentOptimize(mutations, context);

    if (mutations.length > 0) {
      // quill/core/emitter.js, Emitter.events.SCROLL_OPTIMIZE = "scroll-optimize"
      this.emitter.emit("scroll-optimize", mutations, context);
    }
  }

  // Override parchment/src/blot/scroll.ts
  parchmentOptimize(mutations = [], context = {}) {
    // super.optimize(context);
    Reflect.apply(Parchment.Container.prototype.optimize, this, [context]);

    // We must modify mutations directly, cannot make copy and then modify
    // let records = [].slice.call(this.observer.takeRecords());
    let records = [...this.observer.takeRecords()];
    // Array.push currently seems to be implemented by a non-tail recursive function
    // so we cannot just mutations.push.apply(mutations, this.observer.takeRecords());
    while (records.length > 0) {
      mutations.push(records.pop());
    }
    let mark = (blot, markParent) => {
      if (!blot || blot === this) {
        return;
      }
      if (!blot.domNode.parentNode) {
        return;
      }
      if (blot.domNode.__blot && blot.domNode.__blot.mutations === null) {
        blot.domNode.__blot.mutations = [];
      }
      if (markParent) {
        mark(blot.parent);
      }
    };
    let optimize = (blot) => {
      // Post-order traversal
      if (!blot.domNode.__blot) {
        return;
      }

      if (blot instanceof Parchment.Container) {
        blot.children.forEach(optimize);
      }
      blot.optimize(context);
    };
    let remaining = mutations;
    for (let ind = 0; remaining.length > 0; ind += 1) {
      // MAX_OPTIMIZE_ITERATIONS = 100
      if (ind >= 100) {
        throw new Error("[Parchment] Maximum optimize iterations reached");
      }
      remaining.forEach((mutation) => {
        let blot = Parchment.find(mutation.target, true);
        if (!blot) {
          return;
        }
        if (blot.domNode === mutation.target) {
          if (mutation.type === "childList") {
            mark(Parchment.find(mutation.previousSibling, false));

            mutation.addedNodes.forEach((node) => {
              let child = Parchment.find(node, false);
              mark(child, false);
              if (child instanceof Parchment.Container) {
                child.children.forEach(function(grandChild) {
                  mark(grandChild, false);
                });
              }
            });
          } else if (mutation.type === "attributes") {
            mark(blot.prev);
          }
        }
        mark(blot);
      });
      this.children.forEach(optimize);
      remaining = [...this.observer.takeRecords()];
      records = remaining.slice();
      while (records.length > 0) {
        mutations.push(records.pop());
      }
    }
  }
};
Quill.register("blots/scroll", ScrollOverride, true);
Parchment.register(ScrollOverride);

const Delta = Quill.import('delta')
const Break = Quill.import('blots/break')
const Embed = Quill.import('blots/embed')
const Inline = Quill.import('blots/inline')
const Link = Quill.import('formats/link')

/*
* Support for shift enter
* @see https://github.com/quilljs/quill/issues/252
* @see https://codepen.io/mackermedia/pen/gmNwZP
*/
const lineBreak = {
  blotName: 'break',
  tagName: 'BR'
}

class SmartBreak extends Break {
  length () {
    return 1
  }

  value () {
    return '\n'
  }

  insertInto (parent, ref) {
    Embed.prototype.insertInto.call(this, parent, ref)
  }
}

SmartBreak.blotName = lineBreak.blotName
SmartBreak.tagName = lineBreak.tagName

const lineBreakHandle = {
  key: 13,
  shiftKey: true,
  handler:
    function (range) {
      const currentLeaf = this.quill.getLeaf(range.index)[0]
      const nextLeaf = this.quill.getLeaf(range.index + 1)[0]

      this.quill.insertEmbed(range.index, lineBreak.blotName, true, 'user')

      // Insert a second break if:
      // At the end of the editor, OR next leaf has a different parent (<p>)
      if (nextLeaf === null || (currentLeaf.parent !== nextLeaf.parent)) {
        this.quill.insertEmbed(range.index, lineBreak.blotName, true, 'user')
      }

      // Now that we've inserted a line break, move the cursor forward
      this.quill.setSelection(range.index + 1, Quill.sources.SILENT)
    }
}

function lineBreakMatcher () {
  const newDelta = new Delta()
  newDelta.insert({ break: '' })
  return newDelta
}

Quill.register(SmartBreak)

const anchor = {
  blotName: 'anchor',
  tagName: 'SPAN'
}

class Anchor extends Inline {
  static create (value) {
    const node = super.create(value)
    value = this.sanitize(value)
    node.setAttribute('id', value)
    node.className = 'ql-anchor'
    return node
  }

  static sanitize (id) {
    return id.replace(/\s+/g, '-').toLowerCase()
  }

  static formats (domNode) {
    return domNode.getAttribute('id')
  }

  format (name, value) {
    if (name !== this.statics.blotName || !value) return super.format(name, value)
    value = this.constructor.sanitize(value)
    this.domNode.setAttribute('id', value)
  }
}

Anchor.blotName = anchor.blotName
Anchor.tagName = anchor.tagName

Quill.register(Anchor)

/* Customize Link */
class MyLink extends Link {
  static create (value) {
    const node = super.create(value)
    value = this.sanitize(value)
    node.setAttribute('href', value)

    // relative urls wont have target blank
    const urlPattern = /^((http|https|ftp):\/\/)/
    if (!urlPattern.test(value)) {
      node.removeAttribute('target')
    }

    // url starting with the front-end base url wont have target blank
    if (window[process.env.VUE_APP_NAME].STORE.form.baseUrl) {
      if (value.startsWith(window[process.env.VUE_APP_NAME].STORE.form.baseUrl)) {
        node.removeAttribute('target')
      }
    }

    return node
  }

  format (name, value) {
    super.format(name, value)

    if (name !== this.statics.blotName || !value) {
      return
    }

    // relative urls wont have target blank
    const urlPattern = /^((http|https|ftp):\/\/)/
    if (!urlPattern.test(value)) {
      this.domNode.removeAttribute('target')
      return
    }

    // url starting with the front-end base url wont have target blank
    if (window[process.env.VUE_APP_NAME].STORE.form.baseUrl) {
      if (value.startsWith(window[process.env.VUE_APP_NAME].STORE.form.baseUrl)) {
        this.domNode.removeAttribute('target')
        return
      }
    }

    this.domNode.setAttribute('target', '_blank')
  }
}

Quill.register(MyLink)

/* Custom Icons */
function getIcon (shape) {
  return '<span class="icon icon--wysiwyg_' + shape + '" aria-hidden="true"><svg><title>' + shape + '</title><use xlink:href="#icon--wysiwyg_' + shape + '"></use></svg></span>'
}

const icons = Quill.import('ui/icons') // custom icons
icons.bold = getIcon('bold')
icons.italic = getIcon('italic')
icons.italic = getIcon('italic')
icons.anchor = getIcon('anchor')
icons.link = getIcon('link')
icons.header['1'] = getIcon('header')
icons.header['2'] = getIcon('header-2')
icons.header['3'] = getIcon('header-3')
icons.header['4'] = getIcon('header-4')
icons.header['5'] = getIcon('header-5')
icons.header['6'] = getIcon('header-6')

/*
* ClipBoard manager
* Use formats to authorize what user can paste
* Formats are based on toolbar configuration
*/

const QuillDefaultFormats = [
  'background',
  'bold',
  'color',
  'font',
  'code',
  'italic',
  'link',
  'size',
  'strike',
  'script',
  'underline',
  'blockquote',
  'header',
  'indent',
  'list',
  'align',
  'direction',
  'code-block',
  'formula',
  'image',
  'video'
]

function getQuillFormats (toolbarEls) {
  const formats = [lineBreak.blotName, anchor.blotName] // Allow linebreak and anchor

  function addFormat (format) {
    if (formats.indexOf(format) > -1 || QuillDefaultFormats.indexOf(format) === -1) return
    formats.push(format)
  }

  toolbarEls.forEach((el) => {
    if (typeof el === 'object') {
      for (const property in el) {
        addFormat(property)
      }
    }

    if (typeof el === 'string') {
      addFormat(el)
    }
  })

  return formats
}

export default {
  Quill: Quill,
  lineBreak: {
    handle: lineBreakHandle,
    clipboard: [lineBreak.tagName, lineBreakMatcher]
  },
  getFormats: getQuillFormats
}
