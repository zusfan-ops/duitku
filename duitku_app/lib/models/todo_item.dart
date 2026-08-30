import 'dart:convert';

class TodoSubtask {
  String title;
  bool done;

  TodoSubtask({required this.title, this.done = false});

  factory TodoSubtask.fromJson(Map<String, dynamic> json) {
    return TodoSubtask(
      title: json['title'] ?? '',
      done: json['done'] == true || json['done'] == 1 || json['done'] == '1',
    );
  }

  Map<String, dynamic> toJson() => {
    'title': title,
    'done': done ? 1 : 0,
  };
}

class TodoItem {
  final int id;
  final int userId;
  String title;
  String? description;
  String category;
  String priority; // 'high', 'medium', 'low'
  String? dueDate;
  String? dueTime;
  bool isCompleted;
  String? completedAt;
  List<TodoSubtask> subtasks;
  String? createdAt;

  TodoItem({
    required this.id,
    required this.userId,
    required this.title,
    this.description,
    this.category = 'Pribadi',
    this.priority = 'medium',
    this.dueDate,
    this.dueTime,
    this.isCompleted = false,
    this.completedAt,
    this.subtasks = const [],
    this.createdAt,
  });

  factory TodoItem.fromJson(Map<String, dynamic> json) {
    List<TodoSubtask> subs = [];
    if (json['subtasks_array'] != null && json['subtasks_array'] is List) {
      subs = (json['subtasks_array'] as List)
          .map((e) => TodoSubtask.fromJson(e is Map<String, dynamic> ? e : {}))
          .toList();
    } else if (json['subtasks'] != null && json['subtasks'] is String && json['subtasks'].toString().isNotEmpty) {
      try {
        final decoded = jsonDecode(json['subtasks']);
        if (decoded is List) {
          subs = decoded.map((e) => TodoSubtask.fromJson(e is Map<String, dynamic> ? e : {})).toList();
        }
      } catch (_) {}
    }

    return TodoItem(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      userId: int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString(),
      category: json['category']?.toString() ?? 'Pribadi',
      priority: json['priority']?.toString() ?? 'medium',
      dueDate: json['due_date']?.toString(),
      dueTime: json['due_time']?.toString(),
      isCompleted: json['is_completed'] == 1 || json['is_completed'] == true || json['is_completed'] == '1',
      completedAt: json['completed_at']?.toString(),
      subtasks: subs,
      createdAt: json['created_at']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'user_id': userId,
    'title': title,
    'description': description,
    'category': category,
    'priority': priority,
    'due_date': dueDate,
    'due_time': dueTime,
    'is_completed': isCompleted ? 1 : 0,
    'completed_at': completedAt,
    'subtasks': subtasks.map((s) => s.toJson()).toList(),
    'created_at': createdAt,
  };
}

class TodoSummary {
  final int totalToday;
  final int completedToday;
  final int pendingToday;
  final int totalAll;
  final int completedAll;
  final int pendingAll;
  final int overdueCount;
  final int completionRate;
  final List<TodoItem> previews;

  TodoSummary({
    this.totalToday = 0,
    this.completedToday = 0,
    this.pendingToday = 0,
    this.totalAll = 0,
    this.completedAll = 0,
    this.pendingAll = 0,
    this.overdueCount = 0,
    this.completionRate = 0,
    this.previews = const [],
  });

  factory TodoSummary.fromJson(Map<String, dynamic> json) {
    List<TodoItem> prevs = [];
    if (json['previews'] != null && json['previews'] is List) {
      prevs = (json['previews'] as List)
          .map((e) => TodoItem.fromJson(e is Map<String, dynamic> ? e : {}))
          .toList();
    }

    return TodoSummary(
      totalToday: int.tryParse(json['total_today']?.toString() ?? '0') ?? 0,
      completedToday: int.tryParse(json['completed_today']?.toString() ?? '0') ?? 0,
      pendingToday: int.tryParse(json['pending_today']?.toString() ?? '0') ?? 0,
      totalAll: int.tryParse(json['total_all']?.toString() ?? '0') ?? 0,
      completedAll: int.tryParse(json['completed_all']?.toString() ?? '0') ?? 0,
      pendingAll: int.tryParse(json['pending_all']?.toString() ?? '0') ?? 0,
      overdueCount: int.tryParse(json['overdue_count']?.toString() ?? '0') ?? 0,
      completionRate: int.tryParse(json['completion_rate']?.toString() ?? '0') ?? 0,
      previews: prevs,
    );
  }
}
