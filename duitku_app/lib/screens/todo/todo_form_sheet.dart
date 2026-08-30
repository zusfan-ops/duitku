import 'package:flutter/material.dart';
import '../../models/todo_item.dart';
import '../../theme.dart';

class TodoFormSheet extends StatefulWidget {
  final TodoItem? initialItem;
  final Function(Map<String, dynamic> data) onSave;

  const TodoFormSheet({
    super.key,
    this.initialItem,
    required this.onSave,
  });

  @override
  State<TodoFormSheet> createState() => _TodoFormSheetState();
}

class _TodoFormSheetState extends State<TodoFormSheet> {
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  final _subtaskInputController = TextEditingController();

  String _category = 'Pribadi';
  String _priority = 'medium';
  DateTime? _dueDate;
  TimeOfDay? _dueTime;
  List<TodoSubtask> _subtasks = [];

  final List<Map<String, dynamic>> _categories = [
    {'name': 'Keuangan', 'icon': '💰', 'color': Color(0xFF10B981)},
    {'name': 'Pekerjaan', 'icon': '💼', 'color': Color(0xFF3B82F6)},
    {'name': 'Pribadi', 'icon': '👤', 'color': Color(0xFF8B5CF6)},
    {'name': 'Belanja', 'icon': '🛒', 'color': Color(0xFFEC4899)},
    {'name': 'Traveling', 'icon': '✈️', 'color': Color(0xFF06B6D4)},
    {'name': 'Kesehatan', 'icon': '❤️', 'color': Color(0xFFEF4444)},
    {'name': 'Lainnya', 'icon': '📝', 'color': Color(0xFF64748B)},
  ];

  @override
  void initState() {
    super.initState();
    if (widget.initialItem != null) {
      _titleController.text = widget.initialItem!.title;
      _descController.text = widget.initialItem!.description ?? '';
      _category = widget.initialItem!.category;
      _priority = widget.initialItem!.priority;
      if (widget.initialItem!.dueDate != null) {
        _dueDate = DateTime.tryParse(widget.initialItem!.dueDate!);
      }
      if (widget.initialItem!.dueTime != null) {
        final parts = widget.initialItem!.dueTime!.split(':');
        if (parts.length >= 2) {
          _dueTime = TimeOfDay(hour: int.tryParse(parts[0]) ?? 0, minute: int.tryParse(parts[1]) ?? 0);
        }
      }
      _subtasks = List.from(widget.initialItem!.subtasks);
    } else {
      _dueDate = DateTime.now();
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _subtaskInputController.dispose();
    super.dispose();
  }

  void _addSubtask() {
    final text = _subtaskInputController.text.trim();
    if (text.isEmpty) return;
    setState(() {
      _subtasks.add(TodoSubtask(title: text, done: false));
      _subtaskInputController.clear();
    });
  }

  void _submit() {
    final title = _titleController.text.trim();
    if (title.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Judul tugas wajib diisi!')),
      );
      return;
    }

    String? dueDateStr;
    if (_dueDate != null) {
      dueDateStr = "${_dueDate!.year.toString().padLeft(4, '0')}-${_dueDate!.month.toString().padLeft(2, '0')}-${_dueDate!.day.toString().padLeft(2, '0')}";
    }

    String? dueTimeStr;
    if (_dueTime != null) {
      dueTimeStr = "${_dueTime!.hour.toString().padLeft(2, '0')}:${_dueTime!.minute.toString().padLeft(2, '0')}:00";
    }

    final data = {
      'title': title,
      'description': _descController.text.trim().isEmpty ? null : _descController.text.trim(),
      'category': _category,
      'priority': _priority,
      'due_date': dueDateStr,
      'due_time': dueTimeStr,
      'subtasks': _subtasks.map((s) => s.toJson()).toList(),
    };

    widget.onSave(data);
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isEditing = widget.initialItem != null;

    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
      ),
      child: Container(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E293B) : Colors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Handle bar
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: Colors.grey.withValues(alpha: 0.3),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),

              // Title Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    isEditing ? 'Edit Tugas' : 'Tugas Baru',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Title Field
              TextField(
                controller: _titleController,
                autofocus: !isEditing,
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                decoration: InputDecoration(
                  hintText: 'Apa yang ingin Anda kerjakan?',
                  filled: true,
                  fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                ),
              ),
              const SizedBox(height: 10),

              // Description Field
              TextField(
                controller: _descController,
                maxLines: 2,
                style: const TextStyle(fontSize: 13),
                decoration: InputDecoration(
                  hintText: 'Catatan tambahan / rincian (opsional)...',
                  filled: true,
                  fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                ),
              ),
              const SizedBox(height: 14),

              // Priority Selector
              const Text('Prioritas', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Colors.grey)),
              const SizedBox(height: 6),
              Row(
                children: [
                  _prioChip('high', '🔥 Tinggi', const Color(0xFFEF4444)),
                  const SizedBox(width: 8),
                  _prioChip('medium', '⚡ Sedang', const Color(0xFFF59E0B)),
                  const SizedBox(width: 8),
                  _prioChip('low', '🌱 Rendah', const Color(0xFF3B82F6)),
                ],
              ),
              const SizedBox(height: 14),

              // Category Selector
              const Text('Kategori', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Colors.grey)),
              const SizedBox(height: 6),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: _categories.map((c) {
                    final isSel = _category == c['name'];
                    return Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: ChoiceChip(
                        label: Text("${c['icon']} ${c['name']}"),
                        selected: isSel,
                        selectedColor: (c['color'] as Color).withValues(alpha: 0.2),
                        labelStyle: TextStyle(
                          fontSize: 11.5,
                          fontWeight: isSel ? FontWeight.w800 : FontWeight.w600,
                          color: isSel ? c['color'] : (isDark ? Colors.white70 : Colors.black87),
                        ),
                        onSelected: (sel) {
                          if (sel) setState(() => _category = c['name']);
                        },
                      ),
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 14),

              // Due Date & Time Picker
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: _dueDate ?? DateTime.now(),
                          firstDate: DateTime(2020),
                          lastDate: DateTime(2035),
                        );
                        if (picked != null) setState(() => _dueDate = picked);
                      },
                      icon: const Icon(Icons.calendar_today_rounded, size: 16),
                      label: Text(
                        _dueDate != null ? "${_dueDate!.day}/${_dueDate!.month}/${_dueDate!.year}" : "Pilih Tanggal",
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                      ),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final picked = await showTimePicker(
                          context: context,
                          initialTime: _dueTime ?? TimeOfDay.now(),
                        );
                        if (picked != null) setState(() => _dueTime = picked);
                      },
                      icon: const Icon(Icons.access_time_rounded, size: 16),
                      label: Text(
                        _dueTime != null ? _dueTime!.format(context) : "Pilih Jam",
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                      ),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Subtasks checklist builder
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Subtugas / Checklist', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Colors.grey)),
                  Text("${_subtasks.where((s)=>s.done).length}/${_subtasks.length}", style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.grey)),
                ],
              ),
              const SizedBox(height: 6),

              // Subtask list
              ..._subtasks.asMap().entries.map((entry) {
                final idx = entry.key;
                final st = entry.value;
                return Container(
                  margin: const EdgeInsets.only(bottom: 6),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    children: [
                      Checkbox(
                        value: st.done,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                        activeColor: const Color(0xFF10B981),
                        onChanged: (val) => setState(() => st.done = val ?? false),
                      ),
                      Expanded(
                        child: Text(
                          st.title,
                          style: TextStyle(
                            fontSize: 12.5,
                            decoration: st.done ? TextDecoration.lineThrough : null,
                            color: st.done ? Colors.grey : null,
                          ),
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete_outline, size: 18, color: Colors.grey),
                        onPressed: () => setState(() => _subtasks.removeAt(idx)),
                      ),
                    ],
                  ),
                );
              }),

              // Subtask input bar
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _subtaskInputController,
                      style: const TextStyle(fontSize: 12.5),
                      decoration: InputDecoration(
                        hintText: 'Tambah subtugas...',
                        filled: true,
                        fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      onSubmitted: (_) => _addSubtask(),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton.filled(
                    onPressed: _addSubtask,
                    icon: const Icon(Icons.add, size: 18),
                    style: IconButton.styleFrom(
                      backgroundColor: const Color(0xFF4F46E5),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    elevation: 4,
                  ),
                  child: Text(
                    isEditing ? 'Simpan Perubahan' : 'Tambah Tugas',
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _prioChip(String key, String label, Color color) {
    final isSel = _priority == key;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _priority = key),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: isSel ? color.withValues(alpha: 0.18) : Colors.transparent,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: isSel ? color : Colors.grey.withValues(alpha: 0.25), width: isSel ? 1.5 : 1),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: isSel ? FontWeight.w800 : FontWeight.w600,
              color: isSel ? color : Colors.grey,
            ),
          ),
        ),
      ),
    );
  }
}
