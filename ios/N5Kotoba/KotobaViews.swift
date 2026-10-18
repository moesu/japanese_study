import SwiftUI

struct KotobaListView: View {
    @Environment(StudyModel.self) private var model
    @State private var query = ""
    @State private var kind = "all"
    @State private var show = ShowMode.both
    @State private var learnedFilter = "all"

    private let kinds: [(String, String)] = [
        ("all", "အားလုံး"),
        ("i-adj", "い-adj"),
        ("na-adj", "な-adj"),
        ("adverb", "adverb"),
        ("verb", "verb"),
        ("noun", "noun"),
        ("other", "other"),
    ]

    private var searched: [KotobaEntry] {
        let needle = query.trimmingCharacters(in: .whitespacesAndNewlines).lowercased()
        guard !needle.isEmpty else { return model.kotoba }
        return model.kotoba.filter { word in
            let haystack = (word.word + word.reading + (word.masu ?? "") + word.en + word.my).lowercased()
            return haystack.contains(needle)
        }
    }

    private var byKind: [KotobaEntry] {
        kind == "all" ? searched : searched.filter { $0.kind == kind }
    }

    private var results: [KotobaEntry] {
        switch learnedFilter {
        case "yes":
            return byKind.filter { model.isLearned($0) }
        case "no":
            return byKind.filter { !model.isLearned($0) }
        default:
            return byKind
        }
    }

    var body: some View {
        NavigationStack {
            ScrollView {
                VStack(alignment: .leading, spacing: 12) {
                    chipRow(kinds.map { ($0.0, "\($0.1) \(count(for: $0.0))") }, selection: $kind)
                    chipRow(ShowMode.allCases.map { ($0.rawValue, $0.rawValue) }, selection: showBinding)
                    chipRow([
                        ("all", "အားလုံး \(byKind.count)"),
                        ("yes", "ကျက်ပြီးပါပြီ \(byKind.filter { model.isLearned($0) }.count)"),
                        ("no", "မကျက်ရသေးပါ \(byKind.filter { !model.isLearned($0) }.count)"),
                    ], selection: $learnedFilter)

                    if results.isEmpty {
                        Text("မတွေ့ပါ။")
                            .foregroundStyle(Ink.muted)
                            .frame(maxWidth: .infinity, alignment: .center)
                            .padding(.top, 32)
                    } else {
                        LazyVGrid(columns: [GridItem(.adaptive(minimum: 150), spacing: 10)], spacing: 10) {
                            ForEach(results) { word in
                                KotobaCard(word: word, show: show)
                            }
                        }
                    }
                }
                .padding(16)
            }
            .background(Ink.paper)
            .navigationTitle("N5 言葉")
            .navigationBarTitleDisplayMode(.inline)
            .searchable(text: $query, prompt: "言葉၊ reading၊ ကျောင်း")
            .toolbar {
                ToolbarItem(placement: .principal) {
                    VStack(spacing: 0) {
                        Text("N5 言葉").font(.headline)
                        Text("\(results.count) / \(model.kotoba.count)")
                            .font(.caption)
                            .foregroundStyle(Ink.muted)
                    }
                }
            }
        }
    }

    private var showBinding: Binding<String> {
        Binding(
            get: { show.rawValue },
            set: { value in show = ShowMode(rawValue: value) ?? .both }
        )
    }

    private func count(for kind: String) -> Int {
        kind == "all" ? searched.count : searched.filter { $0.kind == kind }.count
    }

    private func chipRow(_ items: [(String, String)], selection: Binding<String>) -> some View {
        ScrollView(.horizontal, showsIndicators: false) {
            HStack(spacing: 8) {
                ForEach(items, id: \.0) { item in
                    Button(item.1) { selection.wrappedValue = item.0 }
                        .font(.subheadline)
                        .padding(.horizontal, 12)
                        .padding(.vertical, 7)
                        .background(selection.wrappedValue == item.0 ? Ink.seal : Ink.card, in: Capsule())
                        .foregroundStyle(selection.wrappedValue == item.0 ? Color.white : Ink.text)
                }
            }
        }
    }
}

struct KotobaCard: View {
    @Environment(StudyModel.self) private var model
    let word: KotobaEntry
    let show: ShowMode

    private var usesHiragana: Bool {
        word.shown.unicodeScalars.contains { (0x3040...0x309F).contains($0.value) }
    }

    var body: some View {
        VStack(alignment: .leading, spacing: 6) {
            if show != .my {
                Text(word.shown)
                    .font(.title3)
                    .foregroundStyle(usesHiragana ? Ink.text : Ink.muted)
            }
            if show != .ja {
                Text(word.my)
                    .font(.body)
                    .foregroundStyle(Ink.seal)
            }
            if show == .both {
                Text(word.en)
                    .font(.caption)
                    .foregroundStyle(Ink.muted)
            }
            Button(model.isLearned(word) ? "ကျက်ပြီးပါပြီ" : "မကျက်ရသေးပါ") {
                model.toggleLearned(word)
            }
            .font(.caption.weight(.semibold))
            .padding(.horizontal, 10)
            .padding(.vertical, 5)
            .background(model.isLearned(word) ? Ink.moss : Color.clear, in: Capsule())
            .overlay(Capsule().stroke(model.isLearned(word) ? Ink.moss : Ink.muted, lineWidth: 1))
            .foregroundStyle(model.isLearned(word) ? Color.white : Ink.muted)
        }
        .frame(maxWidth: .infinity, minHeight: 108, alignment: .leading)
        .padding(12)
        .background(Ink.card, in: RoundedRectangle(cornerRadius: 14))
    }
}
