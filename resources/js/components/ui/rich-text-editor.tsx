import React, { useState, useEffect } from 'react'
import { useEditor, EditorContent } from '@tiptap/react'
import StarterKit from '@tiptap/starter-kit'
import { Extension } from '@tiptap/core'

import { Strike } from '@tiptap/extension-strike'
import { Highlight } from '@tiptap/extension-highlight'
import { TextAlign } from '@tiptap/extension-text-align'
import { Link } from '@tiptap/extension-link'
import { Color } from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import { Button } from './button'
import { cn } from '@/lib/utils'
import {
  Bold,
  Italic,
  Underline as UnderlineIcon,
  Strikethrough,
  Highlighter,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  List,
  ListOrdered,
  Quote,
  Undo,
  Redo,
  Link as LinkIcon,
  Table as TableIcon,
  Plus,
  Trash2,
  Heading1,
  Heading2,
  Heading3,
  Baseline,
} from 'lucide-react'
import { useTranslation } from 'react-i18next'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './select'
import { Popover, PopoverContent, PopoverTrigger } from './popover'

// Custom TipTap Extension for Line Height / Line Spacing
export const LineHeight = Extension.create({
  name: 'lineHeight',
  addOptions() {
    return {
      types: ['paragraph', 'heading'],
      defaultLineHeight: 'normal',
    };
  },
  addGlobalAttributes() {
    return [
      {
        types: this.options.types,
        attributes: {
          lineHeight: {
            default: null,
            parseHTML: (element) => element.style.lineHeight || null,
            renderHTML: (attributes) => {
              if (!attributes.lineHeight) {
                return {};
              }
              return {
                style: `line-height: ${attributes.lineHeight}`,
              };
            },
          },
        },
      },
    ];
  },
  addCommands() {
    return {
      setLineHeight:
        (lineHeight: string) =>
        ({ commands }: any) => {
          return this.options.types.every((type: string) =>
            commands.updateAttributes(type, { lineHeight })
          );
        },
      unsetLineHeight:
        () =>
        ({ commands }: any) => {
          return this.options.types.every((type: string) =>
            commands.resetAttributes(type, 'lineHeight')
          );
        },
    };
  },
});

interface TableGridPickerProps {
  editor: any
  onInsertTable: (rows: number, cols: number) => void
}

function TableGridPicker({ editor, onInsertTable }: TableGridPickerProps) {
  const { t } = useTranslation();
  const [hoveredRows, setHoveredRows] = useState(0);
  const [hoveredCols, setHoveredCols] = useState(0);
  const [isOpen, setIsOpen] = useState(false);

  const maxRows = 6;
  const maxCols = 8;

  const handleSelect = (r: number, c: number) => {
    onInsertTable(r, c);
    setIsOpen(false);
    setHoveredRows(0);
    setHoveredCols(0);
  };

  const isTableActive = editor?.isActive('table');

  return (
    <Popover open={isOpen} onOpenChange={setIsOpen}>
      <PopoverTrigger asChild>
        <Button
          type="button"
          variant="ghost"
          size="sm"
          onMouseDown={(e) => e.preventDefault()}
          className={cn(
            "flex items-center gap-1 font-semibold text-xs text-slate-700 hover:bg-slate-100 px-2.5 py-1",
            isTableActive && "bg-blue-50 text-blue-700 font-bold border border-blue-200"
          )}
          title={t('Table')}
        >
          <TableIcon className="h-4 w-4 text-slate-600" />
          <span>{t('Table')}</span>
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-64 p-3 bg-white rounded-xl shadow-xl border border-slate-200 space-y-3" align="start">
        {isTableActive && (
          <div className="space-y-2 border-b border-slate-100 pb-3">
            <div className="text-[11px] font-bold uppercase tracking-wider text-slate-500">
              {t('Table Management')}
            </div>
            
            {/* Rows Controls */}
            <div className="grid grid-cols-2 gap-1.5">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-start gap-1 text-slate-700"
                onClick={() => {
                  editor.chain().focus().addRowBefore().run();
                  setIsOpen(false);
                }}
              >
                <Plus className="h-3 w-3 text-emerald-600" />
                <span>{t('Row Above')}</span>
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-start gap-1 text-slate-700"
                onClick={() => {
                  editor.chain().focus().addRowAfter().run();
                  setIsOpen(false);
                }}
              >
                <Plus className="h-3 w-3 text-emerald-600" />
                <span>{t('Row Below')}</span>
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-start gap-1 text-rose-600 hover:bg-rose-50"
                onClick={() => {
                  editor.chain().focus().deleteRow().run();
                  setIsOpen(false);
                }}
              >
                <Trash2 className="h-3 w-3" />
                <span>{t('Delete Row')}</span>
              </Button>
            </div>

            {/* Columns Controls */}
            <div className="grid grid-cols-2 gap-1.5 pt-1">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-start gap-1 text-slate-700"
                onClick={() => {
                  editor.chain().focus().addColumnBefore().run();
                  setIsOpen(false);
                }}
              >
                <Plus className="h-3 w-3 text-blue-600" />
                <span>{t('Col Left')}</span>
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-start gap-1 text-slate-700"
                onClick={() => {
                  editor.chain().focus().addColumnAfter().run();
                  setIsOpen(false);
                }}
              >
                <Plus className="h-3 w-3 text-blue-600" />
                <span>{t('Col Right')}</span>
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-start gap-1 text-rose-600 hover:bg-rose-50"
                onClick={() => {
                  editor.chain().focus().deleteColumn().run();
                  setIsOpen(false);
                }}
              >
                <Trash2 className="h-3 w-3" />
                <span>{t('Delete Col')}</span>
              </Button>
            </div>

            {/* Table Controls */}
            <div className="pt-1 flex items-center gap-1.5">
              <Button
                type="button"
                variant="outline"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex-1 flex items-center justify-center gap-1 text-slate-700"
                onClick={() => {
                  editor.chain().focus().toggleHeaderRow().run();
                  setIsOpen(false);
                }}
              >
                <TableIcon className="h-3 w-3 text-indigo-600" />
                <span>{t('Toggle Header')}</span>
              </Button>
              <Button
                type="button"
                variant="destructive"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                className="h-7 text-[11px] px-2 flex items-center justify-center gap-1 bg-rose-600 hover:bg-rose-700"
                onClick={() => {
                  editor.chain().focus().deleteTable().run();
                  setIsOpen(false);
                }}
              >
                <Trash2 className="h-3 w-3" />
                <span>{t('Delete Table')}</span>
              </Button>
            </div>
          </div>
        )}

        <div>
          <div className="text-xs font-bold text-slate-700 mb-2 text-center">
            {hoveredRows > 0 && hoveredCols > 0 ? `${hoveredRows} × ${hoveredCols} ${t('Table')}` : t('Insert New Table')}
          </div>
          <div 
            className="grid gap-1 justify-center"
            style={{ gridTemplateColumns: `repeat(${maxCols}, minmax(0, 1fr))` }}
            onMouseLeave={() => {
              setHoveredRows(0);
              setHoveredCols(0);
            }}
          >
            {Array.from({ length: maxRows }).map((_, rIdx) => {
              const r = rIdx + 1;
              return Array.from({ length: maxCols }).map((_, cIdx) => {
                const c = cIdx + 1;
                const isSelected = r <= hoveredRows && c <= hoveredCols;
                return (
                  <div
                    key={`${r}-${c}`}
                    onMouseEnter={() => {
                      setHoveredRows(r);
                      setHoveredCols(c);
                    }}
                    onClick={() => handleSelect(r, c)}
                    className={cn(
                      'w-5 h-5 border rounded-xs cursor-pointer transition-colors',
                      isSelected 
                        ? 'bg-blue-500 border-blue-600' 
                        : 'bg-slate-50 border-slate-300 hover:border-slate-400'
                    )}
                  />
                );
              });
            })}
          </div>
        </div>
      </PopoverContent>
    </Popover>
  );
}

interface RichTextEditorProps {
  content?: string
  onChange?: (content: string) => void
  placeholder?: string
  className?: string
  disabled?: boolean
  minimal?: boolean
  onKeyDown?: (e: React.KeyboardEvent) => void
}

export function RichTextEditor({
  content = '',
  onChange,
  placeholder,
  className,
  disabled = false,
  minimal = false,
  onKeyDown
}: RichTextEditorProps) {
  const { t } = useTranslation();
  const [, setSelectionUpdateCount] = useState(0);

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        bulletList: {
          keepMarks: true,
          keepAttributes: false,
        },
        orderedList: {
          keepMarks: true,
          keepAttributes: false,
        },
        heading: {
          levels: [1, 2, 3, 4],
        },
        strike: false,
        link: false,
      }),

      Strike,
      Highlight.configure({ multicolor: true }),
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
      Link.configure({
        openOnClick: false,
        HTMLAttributes: {
          class: 'text-blue-600 underline cursor-pointer hover:text-blue-800',
        },
      }),
      Color,
      TextStyle,
      LineHeight.configure({ types: ['paragraph', 'heading'] }),
      Table.configure({
        resizable: true,
        HTMLAttributes: {
          class: 'tiptap-table border-collapse w-full table-auto my-4 border border-slate-300',
        },
      }),
      TableRow,
      TableHeader.configure({
        HTMLAttributes: {
          class: 'border border-slate-300 bg-slate-100 p-2.5 text-left font-bold',
        },
      }),
      TableCell.configure({
        HTMLAttributes: {
          class: 'border border-slate-300 p-2.5',
        },
      }),
    ],
    content,
    onUpdate: ({ editor }: any) => {
      onChange?.(editor.getHTML())
    },
    onSelectionUpdate: () => {
      setSelectionUpdateCount((prev) => prev + 1);
    },
    editorProps: {
      attributes: {
        class: cn(
          'focus:outline-none prose prose-sm max-w-none [&_ul]:list-disc [&_ul]:ml-6 [&_ol]:list-decimal [&_ol]:ml-6 [&_li]:my-0.5 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:my-2 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:my-2 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:my-1 [&_table]:w-full [&_table]:table-auto [&_table]:border-collapse [&_table]:my-4 [&_table]:border [&_table]:border-slate-300 [&_th]:border [&_th]:border-slate-300 [&_th]:bg-slate-100 [&_th]:p-2.5 [&_th]:text-left [&_th]:font-bold [&_td]:border [&_td]:border-slate-300 [&_td]:p-2.5',
          '[&_p:empty]:min-h-[1.15em] [&_p:empty]:my-0 [&_p:empty]:before:content-["\\00a0"] [&_p:has(>br:only-child)]:min-h-[1.15em] [&_p:has(>br:only-child)]:my-0',
          minimal ? 'min-h-[36px] p-1.5 text-xs [&_p]:my-0.5' : 'min-h-[140px] p-3 text-sm [&_p]:my-1'
        ),
      },
    },
    editable: !disabled,
  })

  useEffect(() => {
    if (editor && content !== undefined && editor.getHTML() !== content) {
      editor.commands.setContent(content || '');
    }
  }, [content, editor]);

  if (!editor) return null

  const getHeadingValue = () => {
    if (editor.isActive('heading', { level: 1 })) return 'h1';
    if (editor.isActive('heading', { level: 2 })) return 'h2';
    if (editor.isActive('heading', { level: 3 })) return 'h3';
    return 'p';
  };

  const getLineHeightValue = () => {
    const pLineHeight = editor.getAttributes('paragraph').lineHeight;
    const hLineHeight = editor.getAttributes('heading').lineHeight;
    return pLineHeight || hLineHeight || 'normal';
  };

  const isTableActive = editor.isActive('table');

  return (
    <div className={cn('border rounded-md', className)}>
      {minimal ? (
        <div className="border-b p-1 flex flex-wrap items-center gap-1 bg-slate-50/50">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleBold().run()}
            className={cn('h-7 px-2', editor.isActive('bold') ? 'bg-muted font-bold' : '')}
            title={t('Bold')}
          >
            <Bold className="h-3.5 w-3.5" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleItalic().run()}
            className={cn('h-7 px-2', editor.isActive('italic') ? 'bg-muted font-bold' : '')}
            title={t('Italic')}
          >
            <Italic className="h-3.5 w-3.5" />
          </Button>

          <div className="w-px h-4 bg-border mx-1" />

          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleBulletList().run()}
            className={cn('h-7 px-2', editor.isActive('bulletList') ? 'bg-muted font-bold' : '')}
            title={t('Bullet List')}
          >
            <List className="h-3.5 w-3.5" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleOrderedList().run()}
            className={cn('h-7 px-2', editor.isActive('orderedList') ? 'bg-muted font-bold' : '')}
            title={t('Numbered List')}
          >
            <ListOrdered className="h-3.5 w-3.5" />
          </Button>
        </div>
      ) : (
        <div className="border-b p-2 flex flex-wrap items-center gap-1.5 bg-slate-50/50">
          {/* Heading Dropdown */}
          <Select
            value={getHeadingValue()}
            onValueChange={(val) => {
              if (val === 'h1') editor.chain().focus().setHeading({ level: 1 }).run();
              else if (val === 'h2') editor.chain().focus().setHeading({ level: 2 }).run();
              else if (val === 'h3') editor.chain().focus().setHeading({ level: 3 }).run();
              else editor.chain().focus().setParagraph().run();
            }}
          >
            <SelectTrigger className="h-8 w-[120px] text-xs bg-white">
              <SelectValue placeholder={t('Paragraph')} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="p">{t('Paragraph')}</SelectItem>
              <SelectItem value="h1">{t('Heading 1')}</SelectItem>
              <SelectItem value="h2">{t('Heading 2')}</SelectItem>
              <SelectItem value="h3">{t('Heading 3')}</SelectItem>
            </SelectContent>
          </Select>

          {/* Line Height / Line Spacing Dropdown */}
          <Select
            value={getLineHeightValue()}
            onValueChange={(val) => {
              if (val === 'normal') {
                (editor.chain().focus() as any).unsetLineHeight().run();
              } else {
                (editor.chain().focus() as any).setLineHeight(val).run();
              }
            }}
          >
            <SelectTrigger className="h-8 w-[115px] text-xs bg-white gap-1.5" title={t('Line Spacing')}>
              <Baseline className="h-3.5 w-3.5 text-slate-500 shrink-0" />
              <SelectValue placeholder={t('Spacing')} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="normal">{t('Normal')}</SelectItem>
              <SelectItem value="1.0">{t('Single (1.0)')}</SelectItem>
              <SelectItem value="1.2">{t('1.2')}</SelectItem>
              <SelectItem value="1.4">{t('1.4')}</SelectItem>
              <SelectItem value="1.6">{t('1.6')}</SelectItem>
              <SelectItem value="1.8">{t('1.8')}</SelectItem>
              <SelectItem value="2.0">{t('Double (2.0)')}</SelectItem>
              <SelectItem value="2.5">{t('2.5')}</SelectItem>
            </SelectContent>
          </Select>

          {/* Quick Heading Buttons */}
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleHeading({ level: 1 }).run()}
            className={editor.isActive('heading', { level: 1 }) ? 'bg-muted font-bold' : ''}
            title={t('Heading 1')}
          >
            <Heading1 className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
            className={editor.isActive('heading', { level: 2 }) ? 'bg-muted font-bold' : ''}
            title={t('Heading 2')}
          >
            <Heading2 className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()}
            className={editor.isActive('heading', { level: 3 }) ? 'bg-muted font-bold' : ''}
            title={t('Heading 3')}
          >
            <Heading3 className="h-4 w-4" />
          </Button>

          <div className="w-px h-6 bg-border mx-0.5" />
          <TableGridPicker editor={editor} onInsertTable={(rows, cols) => {
            editor.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
          }} />

          {/* Add / Delete Row & Column options shown reactively when cursor is inside a table */}
          {isTableActive && (
            <div className="flex items-center gap-0.5 bg-blue-50/80 p-0.5 rounded-lg border border-blue-200/80 ms-1 transition-all">
              <span className="text-[10px] font-bold text-blue-700 px-1.5 py-0.5 uppercase tracking-wider">
                {t('Table')}
              </span>
              <div className="w-px h-4 bg-blue-200 mx-0.5" />

              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().addColumnBefore().run()}
                title={t('Add Column Left')}
                className="h-7 text-xs px-2 text-slate-700 hover:bg-white hover:text-blue-600"
              >
                <Plus className="h-3 w-3 text-blue-600 me-0.5" />
                <span>{t('Col Left')}</span>
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().addColumnAfter().run()}
                title={t('Add Column Right')}
                className="h-7 text-xs px-2 text-slate-700 hover:bg-white hover:text-blue-600"
              >
                <Plus className="h-3 w-3 text-blue-600 me-0.5" />
                <span>{t('Col Right')}</span>
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().deleteColumn().run()}
                title={t('Delete Column')}
                className="h-7 text-xs px-2 text-rose-600 hover:bg-rose-100/80"
              >
                <Trash2 className="h-3 w-3 me-0.5" />
                <span>{t('Del Col')}</span>
              </Button>

              <div className="w-px h-4 bg-blue-200 mx-0.5" />

              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().addRowBefore().run()}
                title={t('Add Row Above')}
                className="h-7 text-xs px-2 text-slate-700 hover:bg-white hover:text-emerald-600"
              >
                <Plus className="h-3 w-3 text-emerald-600 me-0.5" />
                <span>{t('Row Above')}</span>
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().addRowAfter().run()}
                title={t('Add Row Below')}
                className="h-7 text-xs px-2 text-slate-700 hover:bg-white hover:text-emerald-600"
              >
                <Plus className="h-3 w-3 text-emerald-600 me-0.5" />
                <span>{t('Row Below')}</span>
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().deleteRow().run()}
                title={t('Delete Row')}
                className="h-7 text-xs px-2 text-rose-600 hover:bg-rose-100/80"
              >
                <Trash2 className="h-3 w-3 me-0.5" />
                <span>{t('Del Row')}</span>
              </Button>

              <div className="w-px h-4 bg-blue-200 mx-0.5" />

              <Button
                type="button"
                variant="ghost"
                size="sm"
                onMouseDown={(e) => e.preventDefault()}
                onClick={() => editor.chain().focus().deleteTable().run()}
                title={t('Delete Table')}
                className="h-7 text-xs px-2 text-rose-600 hover:bg-rose-100/80 font-bold"
              >
                <Trash2 className="h-3 w-3 me-0.5" />
                <span>{t('Del Table')}</span>
              </Button>
            </div>
          )}

          <div className="w-px h-6 bg-border mx-0.5" />
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleBold().run()}
            className={editor.isActive('bold') ? 'bg-muted' : ''}
          >
            <Bold className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleItalic().run()}
            className={editor.isActive('italic') ? 'bg-muted' : ''}
          >
            <Italic className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleUnderline().run()}
            className={editor.isActive('underline') ? 'bg-muted' : ''}
          >
            <UnderlineIcon className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleStrike().run()}
            className={editor.isActive('strike') ? 'bg-muted' : ''}
          >
            <Strikethrough className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleHighlight().run()}
            className={editor.isActive('highlight') ? 'bg-muted' : ''}
          >
            <Highlighter className="h-4 w-4" />
          </Button>
          <div className="w-px h-6 bg-border mx-0.5" />
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().setTextAlign('left').run()}
            className={editor.isActive({ textAlign: 'left' }) ? 'bg-muted' : ''}
          >
            <AlignLeft className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().setTextAlign('center').run()}
            className={editor.isActive({ textAlign: 'center' }) ? 'bg-muted' : ''}
          >
            <AlignCenter className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().setTextAlign('right').run()}
            className={editor.isActive({ textAlign: 'right' }) ? 'bg-muted' : ''}
          >
            <AlignRight className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().setTextAlign('justify').run()}
            className={editor.isActive({ textAlign: 'justify' }) ? 'bg-muted' : ''}
          >
            <AlignJustify className="h-4 w-4" />
          </Button>
          <div className="w-px h-6 bg-border mx-0.5" />
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleBulletList().run()}
            className={editor.isActive('bulletList') ? 'bg-muted' : ''}
          >
            <List className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleOrderedList().run()}
            className={editor.isActive('orderedList') ? 'bg-muted' : ''}
          >
            <ListOrdered className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().toggleBlockquote().run()}
            className={editor.isActive('blockquote') ? 'bg-muted' : ''}
          >
            <Quote className="h-4 w-4" />
          </Button>
          <div className="w-px h-6 bg-border mx-0.5" />
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => {
              const previousUrl = editor.getAttributes('link').href
              const url = window.prompt(t('URL'), previousUrl)

              if (url === null) {
                return
              }

              if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run()
                return
              }

              editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
            }}
            className={editor.isActive('link') ? 'bg-muted' : ''}
          >
            <LinkIcon className="h-4 w-4" />
          </Button>
          <input
            type="color"
            onInput={(e) => editor.chain().focus().setColor((e.target as HTMLInputElement).value).run()}
            value={editor.getAttributes('textStyle').color || '#000000'}
            className="w-8 h-8 border rounded cursor-pointer"
            title={t('Text Color')}
          />
          <div className="w-px h-6 bg-border mx-0.5" />
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().undo().run()}
            disabled={!editor.can().undo()}
          >
            <Undo className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => editor.chain().focus().redo().run()}
            disabled={!editor.can().redo()}
          >
            <Redo className="h-4 w-4" />
          </Button>
        </div>
      )}

      <div className={cn(
        "prose prose-sm max-w-none resize-y overflow-auto",
        minimal ? "min-h-[38px] p-1.5 [&_p]:my-0.5 text-xs" : "min-h-[140px] p-3 text-sm [&_p]:my-1",
        "[&_p:empty]:min-h-[1.15em] [&_p:empty]:my-0 [&_p:empty]:before:content-['\\00a0'] [&_p:has(>br:only-child)]:min-h-[1.15em] [&_p:has(>br:only-child)]:my-0",
        "[&_ul]:list-disc [&_ul]:ml-6 [&_ol]:list-decimal [&_ol]:ml-6 [&_li]:my-0.5",
        "[&_a]:text-blue-600 [&_a]:underline [&_a]:cursor-pointer hover:[&_a]:text-blue-800",
        "[&_h1]:text-2xl [&_h1]:font-bold [&_h1]:my-2 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:my-2 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:my-1",
        "[&_.ProseMirror]:outline-none"
      )}>
        <EditorContent
          editor={editor}
          placeholder={placeholder || t('Start typing...')}
        />
      </div>
    </div>
  )
}

export default RichTextEditor;