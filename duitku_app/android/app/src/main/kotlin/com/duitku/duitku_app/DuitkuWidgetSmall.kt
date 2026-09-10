package com.duitku.duitku_app

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.util.Log
import android.widget.RemoteViews
import es.antonborri.home_widget.HomeWidgetProvider

/**
 * Widget compact 4x1 — menampilkan saldo, pemasukan, dan pengeluaran
 * dalam format ringkas untuk home screen.
 */
class DuitkuWidgetSmall : HomeWidgetProvider() {

    override fun onUpdate(
        context: Context,
        appWidgetManager: AppWidgetManager,
        appWidgetIds: IntArray,
        widgetData: SharedPreferences,
    ) {
        for (appWidgetId in appWidgetIds) {
            try {
                val views = RemoteViews(context.packageName, R.layout.duitku_widget_small)

                val balance = widgetData.getString(KEY_BALANCE, context.getString(R.string.widget_balance_default))
                    ?: context.getString(R.string.widget_balance_default)
                val income = widgetData.getString(KEY_INCOME, context.getString(R.string.widget_income_default))
                    ?: context.getString(R.string.widget_income_default)
                val expense = widgetData.getString(KEY_EXPENSE, context.getString(R.string.widget_expense_default))
                    ?: context.getString(R.string.widget_expense_default)
                val month = widgetData.getString(KEY_MONTH, context.getString(R.string.widget_month_default))
                    ?: context.getString(R.string.widget_month_default)

                Log.d(TAG, "Small widget [$appWidgetId]: balance=$balance, income=$income, expense=$expense, month=$month")

                views.setTextViewText(R.id.widget_small_balance, balance)
                views.setTextViewText(R.id.widget_small_income, income)
                views.setTextViewText(R.id.widget_small_expense, expense)
                views.setTextViewText(R.id.widget_small_month, month)

                val launchIntent = Intent(context, MainActivity::class.java).apply {
                    flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
                }
                val pendingIntent = PendingIntent.getActivity(
                    context,
                    appWidgetId,
                    launchIntent,
                    PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
                )
                views.setOnClickPendingIntent(R.id.widget_small_root, pendingIntent)

                appWidgetManager.updateAppWidget(appWidgetId, views)
            } catch (e: Exception) {
                Log.e(TAG, "Small widget [$appWidgetId] onUpdate failed", e)
            }
        }
    }

    companion object {
        private const val TAG = "DuitkuWidgetSmall"
        private const val KEY_BALANCE = "widget_balance"
        private const val KEY_INCOME = "widget_income"
        private const val KEY_EXPENSE = "widget_expense"
        private const val KEY_MONTH = "widget_month"
    }
}
