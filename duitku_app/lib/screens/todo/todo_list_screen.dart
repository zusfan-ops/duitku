import 'package:flutter/material.dart';
import '../../models/todo_item.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import 'todo_form_sheet.dart';

class TodoListScreen extends StatefulWidget {
  const TodoListScreen({super.key});

  @override
  State<TodoListScreen> createState() => _TodoListScreenState();
}

class _TodoListScreenState extends State<TodoListScreen> {
  final _api = ApiService.instance;
  final _quickController = TextEditingController();

  List<TodoItem> _tasks = [];
  TodoSummary _summary = TodoSummary();
  bool _loading = true;
  String _filter = 'all';
  String _category = 'all';
  String _search = '';

  final List<Map<String, dynamic>> _categories = [
    {'name': 'all', 'label': 'Semua', 'icon': '📋'},
    {'name': 'Keuangan', 'label': 'Keuangan', 'icon': '💰'},
    {'name': 'Pekerjaan', 'label': 'Pekerjaan', 'icon': '💼'},
    {'name': 'Pribadi', 'label': 'Pribadi', 'icon': '👤'},
    {'name': 'Belanja', 'label': 'Belanja', 'icon': '🛒'},
    {'name': 'Traveling', 'label': 'Traveling', 'icon': '✈️'},
    {'name': 'Kesehatan', 'label': 'Kesehatan', 'icon': '❤️'},
    {'name': 'Lainnya', 'label': 'Lainnya', 'icon': '📝'},
  ];

  @override
  void initState() {
    super.initState();
    _loadTasks();
  }

  @override
  void dispose() {
    _quickController.dispose();
    super.dispose();
  }

  Future<void> _loadTasks() async {
    setState(() => _loading = true);
    try {
      final res = await _api.getTodos(
        filter: _filter,
        category: _category,
        search: _search,
      );
      if (res['success'] == true) {
        final list = (res['data'] as List? ?? [])
            .map((e) => TodoItem.fromJson(e is Map<String, dynamic> ? e : {}))
            .toList();
        final sum = TodoSummary.fromJson(res['summary'] is Map<String, dynamic> ? res['summary'] : {});
        setState(() {
          _tasks = list;
          _summary = sum;
          _loading = false;
        });
      } else {
        setState(() => _loading = false);
      }
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  Future<void> _toggleTask(TodoItem task) async {
    final oldState = task.isCompleted;
    setState(() {
      task.isCompleted = !oldState;
    });

    try {
      final res = await _api.toggleTodo(task.id);
      if (res['success'] != true) {
        setState(() => task.isCompleted = oldState);
      } else {
        _loadTasks();
      }
    } catch (_) {
      setState(() => task.isCompleted = oldState);
    }
  }

  Future<void> _quickAdd() async {
    final text = _quickController.text.trim();
    if (text.isEmpty) return;

    _quickController.clear();
    FocusScope.of(context).unfocus();

    try {
      final now = DateTime.now();
      final dateStr = "${now.year.toString().padLeft(4, '0')}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
      await _api.createTodo({
        'title': text,
        'category': _category != 'all' ? _category : 'Pribadi',
        'priority': 'medium',
        'due_date': dateStr,
      });
      _loadTasks();
    } catch (_) {}
  }

  Future<void> _deleteTask(TodoItem task) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Tugas', style: TextStyle(fontWeight: FontWeight.w800)),
        content: Text('Yakin ingin menghapus "${task.title}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
        ],
      ),
    );

    if (ok == true) {
      try {
        await _api.deleteTodo(task.id);
        _loadTasks();
      } catch (_) {}
    }
  }

  void _openFormSheet([TodoItem? task]) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => TodoFormSheet(
        initialItem: task,
        onSave: (data) async {
          if (task != null) {
            data['id'] = task.id;
          }
          await _api.createTodo(data);
          _loadTasks();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Rencana & Todo', style: TextStyle(fontWeight: FontWeight.w900)),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_circle_outline_rounded, size: 24),
            onPressed: () => _openFormSheet(),
            tooltip: 'Tugas Baru',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadTasks,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
          children: [
            // ── Hero Summary Gradient Card ──
            _buildHeroCard(),
            const SizedBox(height: 14),

            // ── Quick Input Bar ──
            _buildQuickInput(isDark),
            const SizedBox(height: 14),

            // ── Filter Tabs ──
            _buildFilterTabs(isDark),
            const SizedBox(height: 10),

            // ── Category Horizontal Strip ──
            _buildCategoryStrip(isDark),
            const SizedBox(height: 12),

            // ── Task List ──
            if (_loading)
              const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()))
            else if (_tasks.isEmpty)
              _buildEmptyState(isDark)
            else
              ..._tasks.map((t) => _buildTaskCard(t, isDark)),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openFormSheet(),
        backgroundColor: const Color(0xFF4F46E5),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Tugas Baru', style: TextStyle(fontWeight: FontWeight.w800)),
      ),
    );
  }

  Widget _buildHeroCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF3730A3), Color(0xFF4F46E5), Color(0xFF7C3AED)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF4F46E5).withValues(alpha: 0.35),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'TARGET & TUGAS',
                style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Colors.white70, letterSpacing: 0.8),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  "${_summary.completionRate}% Selesai",
                  style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900, color: Colors.white),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            "${_summary.completedAll} / ${_summary.totalAll} Tugas Selesai",
            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: -0.5),
          ),
          const SizedBox(height: 4),
          Text(
            "${_summary.pendingAll} tugas aktif · ${_summary.overdueCount} jatuh tempo",
            style: const TextStyle(fontSize: 12, color: Colors.white70, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 14),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: _summary.totalAll > 0 ? _summary.completedAll / _summary.totalAll : 0,
              minHeight: 8,
              backgroundColor: Colors.white.withValues(alpha: 0.2),
              valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF10B981)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickInput(bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: isDark ? Colors.white10 : const Color(0xFFE2E8F0)),
        boxShadow: AppColors.cardShadow,
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _quickController,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
              decoration: const InputDecoration(
                hintText: 'Tambah tugas cepat...',
                border: InputBorder.none,
                contentPadding: EdgeInsets.symmetric(vertical: 12),
              ),
              onSubmitted: (_) => _quickAdd(),
            ),
          ),
          IconButton(
            onPressed: _quickAdd,
            icon: const Icon(Icons.arrow_upward_rounded, color: Color(0xFF4F46E5)),
            tooltip: 'Tambah',
          ),
        ],
      ),
    );
  }

  Widget _buildFilterTabs(bool isDark) {
    final tabs = [
      {'key': 'all', 'label': 'Semua (${_summary.totalAll})'},
      {'key': 'today', 'label': 'Hari Ini (${_summary.totalToday})'},
      {'key': 'high', 'label': '🔥 Tinggi'},
      {'key': 'completed', 'label': '✅ Selesai (${_summary.completedAll})'},
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: tabs.map((t) {
          final isSel = _filter == t['key'];
          return Padding(
            padding: const EdgeInsets.only(right: 6),
            child: GestureDetector(
              onTap: () {
                setState(() => _filter = t['key']!);
                _loadTasks();
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: isSel ? const Color(0xFF4F46E5) : (isDark ? const Color(0xFF1E293B) : Colors.white),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: isSel ? const Color(0xFF4F46E5) : (isDark ? Colors.white10 : const Color(0xFFE2E8F0))),
                ),
                child: Text(
                  t['label']!,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: isSel ? FontWeight.w800 : FontWeight.w600,
                    color: isSel ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildCategoryStrip(bool isDark) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: _categories.map((c) {
          final isSel = _category == c['name'];
          return Padding(
            padding: const EdgeInsets.only(right: 6),
            child: GestureDetector(
              onTap: () {
                setState(() => _category = c['name']!);
                _loadTasks();
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: isSel ? const Color(0xFF10B981).withValues(alpha: 0.18) : Colors.transparent,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: isSel ? const Color(0xFF10B981) : Colors.grey.withValues(alpha: 0.25)),
                ),
                child: Text(
                  "${c['icon']} ${c['label']}",
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: isSel ? FontWeight.w800 : FontWeight.w600,
                    color: isSel ? const Color(0xFF10B981) : Colors.grey,
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildTaskCard(TodoItem task, bool isDark) {
    Color prioColor;
    String prioLabel;
    if (task.priority == 'high') {
      prioColor = const Color(0xFFEF4444);
      prioLabel = '🔥 Tinggi';
    } else if (task.priority == 'low') {
      prioColor = const Color(0xFF3B82F6);
      prioLabel = '🌱 Rendah';
    } else {
      prioColor = const Color(0xFFF59E0B);
      prioLabel = '⚡ Sedang';
    }

    final isDone = task.isCompleted;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: isDark ? Colors.white10 : const Color(0xFFE2E8F0)),
        boxShadow: AppColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Checkbox button
              GestureDetector(
                onTap: () => _toggleTask(task),
                child: Container(
                  width: 24,
                  height: 24,
                  margin: const EdgeInsets.only(top: 2),
                  decoration: BoxDecoration(
                    color: isDone ? const Color(0xFF10B981) : Colors.transparent,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isDone ? const Color(0xFF10B981) : Colors.grey.shade400,
                      width: 2,
                    ),
                  ),
                  child: isDone ? const Icon(Icons.check, size: 14, color: Colors.white) : null,
                ),
              ),
              const SizedBox(width: 12),

              // Title and desc
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      task.title,
                      style: TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w800,
                        decoration: isDone ? TextDecoration.lineThrough : null,
                        color: isDone ? Colors.grey : (isDark ? Colors.white : const Color(0xFF0F172A)),
                      ),
                    ),
                    if (task.description != null && task.description!.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        task.description!,
                        style: const TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                    ],
                  ],
                ),
              ),

              // Edit & Delete Popup Menu
              PopupMenuButton<String>(
                icon: const Icon(Icons.more_vert_rounded, size: 18, color: Colors.grey),
                onSelected: (val) {
                  if (val == 'edit') _openFormSheet(task);
                  if (val == 'delete') _deleteTask(task);
                },
                itemBuilder: (ctx) => [
                  const PopupMenuItem(value: 'edit', child: Row(children: [Icon(Icons.edit_outlined, size: 16), SizedBox(width: 8), Text('Edit')])),
                  const PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete_outline, size: 16, color: Colors.red), SizedBox(width: 8), Text('Hapus', style: TextStyle(color: Colors.red))])),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Tags Row
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: prioColor.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  prioLabel,
                  style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: prioColor),
                ),
              ),
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  task.category,
                  style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700),
                ),
              ),
              if (task.dueDate != null) ...[
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    "🗓️ ${task.dueDate}",
                    style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ],
          ),

          // Subtasks progress bar
          if (task.subtasks.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.checklist_rounded, size: 14, color: Colors.grey),
                  const SizedBox(width: 6),
                  Text(
                    "Subtugas: ${task.subtasks.where((s) => s.done).length}/${task.subtasks.length}",
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.grey),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildEmptyState(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(40),
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: isDark ? Colors.white10 : const Color(0xFFE2E8F0)),
      ),
      child: const Column(
        children: [
          Text('🎯', style: TextStyle(fontSize: 40)),
          SizedBox(height: 10),
          Text('Belum Ada Tugas', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
          SizedBox(height: 4),
          Text('Semua rencana telah selesai atau belum ditambahkan.', style: TextStyle(fontSize: 12, color: Colors.grey), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}
