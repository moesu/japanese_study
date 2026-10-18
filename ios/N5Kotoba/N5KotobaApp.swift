import SwiftUI

@main
struct N5KotobaApp: App {
    @State private var model = StudyModel()

    var body: some Scene {
        WindowGroup {
            RootView()
                .environment(model)
        }
    }
}

struct RootView: View {
    var body: some View {
        TabView {
            KanjiListView()
                .tabItem { Label("漢字", systemImage: "character.book.closed") }
            KotobaListView()
                .tabItem { Label("言葉", systemImage: "text.book.closed") }
            QuizView()
                .tabItem { Label("စမ်းမေး", systemImage: "questionmark.circle") }
        }
        .tint(Ink.seal)
    }
}

enum Ink {
    static let paper = Color(red: 0.96, green: 0.94, blue: 0.90)
    static let card = Color.white
    static let text = Color(red: 0.13, green: 0.12, blue: 0.11)
    static let muted = Color(red: 0.42, green: 0.38, blue: 0.34)
    static let seal = Color(red: 0.72, green: 0.18, blue: 0.16)
    static let moss = Color(red: 0.16, green: 0.42, blue: 0.30)
}

enum ShowMode: String, CaseIterable, Identifiable {
    case both = "နှစ်မျိုး"
    case ja = "日本語"
    case my = "မြန်မာ"

    var id: String { rawValue }
}
