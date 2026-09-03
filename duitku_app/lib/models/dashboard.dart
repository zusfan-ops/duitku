import 'bill.dart';
import 'category.dart';
import 'debt.dart';
import 'tv_channel.dart';
import 'wallet.dart';

class DashboardData {
  final double balance;
  final double monthlyIncome;
  final double monthlyExpense;
  final List<dynamic> recent;
  final List<Category> categories;
  final String currency;
  final String symbol;
  final String month;
  final String monthKey;
  final double budget;
  final double budgetPct;
  final String savingsName;
  final double savingsTarget;
  final double savingsSaved;
  final double savingsPct;
  final String monthNote;
  final DebtSummary debtSummary;
  final List<dynamic> topCategories;
  final List<Wallet> wallets;
  final List<dynamic> dailyBalance;
  final List<Bill> upcomingBills;
  final List<dynamic> upcomingDebts;
  final List<dynamic> upcomingTaxes;
  final List<dynamic> upcomingRecurring;
  final List<dynamic> notifications;
  final Map<String, dynamic> business;
  final List<TvChannel> tvChannels;
  final Map<String, dynamic> myHomeSummary;

  DashboardData({
    this.balance = 0,
    this.monthlyIncome = 0,
    this.monthlyExpense = 0,
    this.recent = const [],
    this.categories = const [],
    this.currency = 'IDR',
    this.symbol = 'Rp',
    this.month = '',
    this.monthKey = '',
    this.budget = 0,
    this.budgetPct = 0,
    this.savingsName = '',
    this.savingsTarget = 0,
    this.savingsSaved = 0,
    this.savingsPct = 0,
    this.monthNote = '',
    this.debtSummary = const DebtSummary(),
    this.topCategories = const [],
    this.wallets = const [],
    this.dailyBalance = const [],
    this.upcomingBills = const [],
    this.upcomingDebts = const [],
    this.upcomingTaxes = const [],
    this.upcomingRecurring = const [],
    this.notifications = const [],
    this.business = const {},
    this.tvChannels = const [],
    this.myHomeSummary = const {},
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    final monthly = json['monthly'] as Map<String, dynamic>? ?? {};
    return DashboardData(
      balance: double.tryParse('${json['balance']}') ?? 0,
      monthlyIncome: double.tryParse('${monthly['income']}') ?? 0,
      monthlyExpense: double.tryParse('${monthly['expense']}') ?? 0,
      recent: json['recent'] as List<dynamic>? ?? [],
      categories: (json['categories'] as List<dynamic>? ?? [])
          .map((e) => Category.fromJson(e as Map<String, dynamic>))
          .toList(),
      currency: json['currency']?.toString() ?? 'IDR',
      symbol: json['symbol']?.toString() ?? 'Rp',
      month: json['month']?.toString() ?? '',
      monthKey: json['monthKey']?.toString() ?? '',
      budget: double.tryParse('${json['budget']}') ?? 0,
      budgetPct: double.tryParse('${json['budgetPct']}') ?? 0,
      savingsName: json['savingsName']?.toString() ?? '',
      savingsTarget: double.tryParse('${json['savingsTarget']}') ?? 0,
      savingsSaved: double.tryParse('${json['savingsSaved']}') ?? 0,
      savingsPct: double.tryParse('${json['savingsPct']}') ?? 0,
      monthNote: json['monthNote']?.toString() ?? '',
      debtSummary: DebtSummary.fromJson(json['debtSummary'] as Map<String, dynamic>? ?? {}),
      topCategories: json['topCategories'] as List<dynamic>? ?? [],
      wallets: (json['wallets'] as List<dynamic>? ?? [])
          .map((e) => Wallet.fromJson(e as Map<String, dynamic>))
          .toList(),
      dailyBalance: json['dailyBalance'] as List<dynamic>? ?? [],
      upcomingBills: (json['upcomingBills'] as List<dynamic>? ?? [])
          .map((e) => Bill.fromJson(e as Map<String, dynamic>))
          .toList(),
      upcomingDebts: json['upcomingDebts'] as List<dynamic>? ?? [],
      upcomingTaxes: json['upcomingTaxes'] as List<dynamic>? ?? [],
      upcomingRecurring: json['upcomingRecurring'] as List<dynamic>? ?? [],
      notifications: json['notifications'] as List<dynamic>? ?? [],
      business: json['business'] as Map<String, dynamic>? ?? {},
      tvChannels: (json['tv_channels'] as List<dynamic>? ?? [])
          .map((e) => TvChannel.fromJson(e as Map<String, dynamic>))
          .toList(),
      myHomeSummary: json['my_home_summary'] as Map<String, dynamic>? ??
          json['myHomeSummary'] as Map<String, dynamic>? ??
          {},
    );
  }
}
