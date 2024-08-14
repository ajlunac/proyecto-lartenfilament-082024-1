<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administrador';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->hiddenOn('edit')
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->required(),
                Select::make('roles')->multiple()->relationship('roles', 'name')
                    ->preload(),
                Select::make('permissions')
                    ->multiple()
                    ->relationship(titleAttribute: 'name')->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('email_verified_at')->dateTime('d-M-Y'),
                TextColumn::make('roles.name'),
            ])
            ->filters([
                //
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\Action::make('Verify')
                    ->icon('heroicon-m-check-badge')
                    ->action(function (User $user){
                        $user->email_verified_at = Date('Y-m-d H:i:s');
                        $user->save();
                    }),
                Tables\Actions\Action::make('UnVerify')
                    ->icon('heroicon-m-x-circle')
                    ->action(function (User $user){
                        $user->email_verified_at = null;
                        $user->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
