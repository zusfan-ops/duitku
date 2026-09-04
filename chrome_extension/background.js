// DuitKu Chrome Extension - Background Service Worker (Manifest V3)

const DEFAULT_SERVER_URL = "https://duitku.ordr.my.id";

chrome.runtime.onInstalled.addListener(() => {
  // Set default server URL if not configured
  chrome.storage.local.get(["serverUrl"], (result) => {
    if (!result.serverUrl) {
      chrome.storage.local.set({ serverUrl: DEFAULT_SERVER_URL });
    }
  });

  // Create context menu for quick transaction recording from selected text
  chrome.contextMenus.create({
    id: "duitku_record_selection",
    title: "Catat ke DuitKu: \"%s\"",
    contexts: ["selection"]
  });
});

chrome.contextMenus.onClicked.addListener((info, tab) => {
  if (info.menuItemId === "duitku_record_selection" && info.selectionText) {
    const rawText = info.selectionText.trim();
    // Extract numbers from selected text (e.g., "Rp 150.000" -> 150000)
    const numericStr = rawText.replace(/[^0-9]/g, "");
    const parsedAmount = numericStr ? parseInt(numericStr, 10) : 0;

    chrome.storage.local.set({
      draftAmount: parsedAmount > 0 ? parsedAmount : "",
      draftNote: rawText.length > 50 ? rawText.substring(0, 50) + "..." : rawText,
      activeTab: "quick-add"
    }, () => {
      chrome.notifications.create({
        type: "basic",
        iconUrl: "icons/icon128.png",
        title: "DuitKu Catat Cepat",
        message: parsedAmount > 0 
          ? `Nominal Rp ${parsedAmount.toLocaleString("id-ID")} siap dicatat. Klik ikon DuitKu di toolbar untuk simpan!`
          : `Catatan "${rawText}" siap dicatat. Klik ikon DuitKu di toolbar.`
      });
    });
  }
});
